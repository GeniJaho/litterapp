<?php

namespace App\Console\Commands;

use App\Models\PhotoItem;
use App\Models\TagType;
use App\Models\User;
use App\Traits\Training\ExcludesNonVisualItems;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class MlGenerateManifest extends Command
{
    use ExcludesNonVisualItems;

    protected $signature = 'ml:generate-manifest
        {--since= : Only include photos tagged since this date (Y-m-d)}
        {--limit= : Limit exported rows (useful for small end-to-end runs)}';

    protected $description = 'Generate a manifest CSV of photos for kNN embedding extraction and upload to S3';

    public function handle(): int
    {
        $since = $this->option('since');
        $limitOption = $this->option('limit');

        $this->components->info('Generating kNN manifest...');

        $rowLimit = null;

        if ($limitOption !== null) {
            $rowLimit = (int) $limitOption;

            if ($rowLimit <= 0) {
                $this->components->error('The --limit option must be a positive integer.');

                return 1;
            }
        }

        $excludedItemIds = $this->getExcludedItemIds();
        $excludedTagIds = $this->getExcludedTagIds();
        $consentingUserIds = User::query()
            ->whereNotNull('settings->consent_to_training_at')
            ->pluck('id');

        if ($consentingUserIds->isEmpty()) {
            $this->components->error('No users have consented to training.');

            return 1;
        }

        $brandTypeId = TagType::query()->where('slug', 'brand')->value('id');
        $contentTypeId = TagType::query()->where('slug', 'content')->value('id');

        $sinceDate = $since ? Carbon::parse($since)->startOfDay() : null;
        $recentlyConsentedUserIds = collect();

        if ($sinceDate !== null) {
            $recentlyConsentedUserIds = User::query()
                ->whereIn('id', $consentingUserIds)
                ->whereNotNull('settings->consent_to_training_at')
                ->where('settings->consent_to_training_at', '>=', $sinceDate->toIso8601String())
                ->pluck('id');
        }

        $this->components->info("Found {$consentingUserIds->count()} consenting user(s), {$excludedItemIds->count()} excluded item(s).");

        if ($sinceDate) {
            $this->components->info("Filtering to photos tagged since {$sinceDate->toDateString()}.");
            $this->components->info("Including all photos from {$recentlyConsentedUserIds->count()} recently consented user(s).");
        }

        if ($rowLimit !== null) {
            $this->components->info("Limiting export to {$rowLimit} row(s).");
        }

        $this->components->info('Counting rows...');
        $totalCount = $this->baseQuery($consentingUserIds, $excludedItemIds, $sinceDate, $recentlyConsentedUserIds)->count();

        $exportCount = $rowLimit !== null
            ? min($totalCount, $rowLimit)
            : $totalCount;

        if ($exportCount === 0) {
            $this->components->warn('No rows matched the query. Nothing to export.');

            return 0;
        }

        $this->components->info("Exporting {$exportCount} rows...");

        $filename = $since
            ? 'manifest_delta_'.now()->format('Y-m-d').'.csv'
            : 'manifest_'.now()->format('Y-m-d').'.csv';

        $localPath = Storage::disk('local')->path($filename);

        /** @var resource $handle */
        $handle = fopen($localPath, 'w');
        fputcsv($handle, ['photo_id', 's3_key', 'item_id', 'brand_tag_ids', 'content_tag_ids']);

        $totalRows = 0;
        $brandCount = 0;
        $contentCount = 0;
        $exportedItemIds = [];

        $progress = $this->output->createProgressBar($exportCount);
        $progress->start();

        $this->baseQuery($consentingUserIds, $excludedItemIds, $sinceDate, $recentlyConsentedUserIds)
            ->with(['tags' => function (Relation $query) use ($excludedTagIds, $brandTypeId, $contentTypeId): void {
                $query->whereIn('tag_type_id', [$brandTypeId, $contentTypeId]);
                if ($excludedTagIds->isNotEmpty()) {
                    $query->whereNotIn('tags.id', $excludedTagIds);
                }
            }])
            ->select(['photo_items.*', 'photos.path', 'photos.user_id'])
            ->chunkById(5000, function (EloquentCollection $photoItems) use ($handle, &$totalRows, &$brandCount, &$contentCount, $brandTypeId, $contentTypeId, $progress, $rowLimit, &$exportedItemIds): bool {
                /** @var EloquentCollection<int, PhotoItem> $photoItems */
                $processedInChunk = 0;

                foreach ($photoItems as $photoItem) {
                    if ($rowLimit !== null && $totalRows >= $rowLimit) {
                        break;
                    }

                    $brandTagIds = $photoItem->tags->where('tag_type_id', $brandTypeId)->pluck('id')->implode('|');
                    $contentTagIds = $photoItem->tags->where('tag_type_id', $contentTypeId)->pluck('id')->implode('|');

                    /** @var string $s3Key */
                    $s3Key = $photoItem->getAttribute('path');

                    fputcsv($handle, [
                        $photoItem->photo_id,
                        $s3Key,
                        $photoItem->item_id,
                        $brandTagIds,
                        $contentTagIds,
                    ]);

                    $totalRows++;
                    $processedInChunk++;
                    $exportedItemIds[(int) $photoItem->item_id] = true;

                    if ($brandTagIds !== '') {
                        $brandCount++;
                    }

                    if ($contentTagIds !== '') {
                        $contentCount++;
                    }
                }

                $progress->advance($processedInChunk);

                return $rowLimit === null || $totalRows < $rowLimit;
            }, 'photo_items.id', 'id');

        $progress->finish();
        fclose($handle);

        $this->newLine();
        $this->components->info('Uploading to S3...');

        $s3Path = "ml/manifests/{$filename}";

        Storage::disk('s3')->putFileAs('ml/manifests', $localPath, $filename);
        Storage::disk('local')->delete($filename);

        $this->newLine();
        $this->components->info('Manifest generation complete.');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total rows', number_format($totalRows)],
                ['Unique items', number_format(count($exportedItemIds))],
                ['Rows with brand tags', $totalRows > 0 ? number_format($brandCount).' ('.round($brandCount / $totalRows * 100, 1).'%)' : '0'],
                ['Rows with content tags', $totalRows > 0 ? number_format($contentCount).' ('.round($contentCount / $totalRows * 100, 1).'%)' : '0'],
            ]
        );

        $this->components->success("Uploaded to S3: {$s3Path}");

        return 0;
    }

    /**
     * @param  Collection<int|string, mixed>  $consentingUserIds
     * @param  Collection<int|string, mixed>  $excludedItemIds
     * @param  Collection<int|string, mixed>  $recentlyConsentedUserIds
     * @return Builder<PhotoItem>
     */
    private function baseQuery(Collection $consentingUserIds, Collection $excludedItemIds, ?Carbon $sinceDate, Collection $recentlyConsentedUserIds): Builder
    {
        return PhotoItem::query()
            ->join('photos', 'photos.id', '=', 'photo_items.photo_id')
            ->whereIn('photos.user_id', $consentingUserIds)
            ->whereNotIn('photo_items.item_id', $excludedItemIds)
            ->when($sinceDate, function (Builder $query) use ($sinceDate, $recentlyConsentedUserIds): void {
                $query->where(function (Builder $deltaQuery) use ($sinceDate, $recentlyConsentedUserIds): void {
                    $deltaQuery->where('photo_items.created_at', '>=', $sinceDate);

                    if ($recentlyConsentedUserIds->isNotEmpty()) {
                        $deltaQuery->orWhereIn('photos.user_id', $recentlyConsentedUserIds);
                    }
                });
            });
    }
}
