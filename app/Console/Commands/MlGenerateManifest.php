<?php

namespace App\Console\Commands;

use App\Models\PhotoItem;
use App\Models\TagType;
use App\Models\User;
use App\Traits\Training\ExcludesNonVisualItems;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class MlGenerateManifest extends Command
{
    use ExcludesNonVisualItems;

    protected $signature = 'ml:generate-manifest {--since= : Only include photos tagged since this date (Y-m-d)}';

    protected $description = 'Generate a manifest CSV of photos for kNN embedding extraction and upload to S3';

    public function handle(): int
    {
        $since = $this->option('since');

        $this->components->info('Generating kNN manifest...');

        $excludedItemIds = $this->getExcludedItemIds();
        $excludedTagIds = $this->getExcludedTagIds();
        $consentingUserIds = User::query()
            ->where('settings->consent_to_training', true)
            ->pluck('id');

        if ($consentingUserIds->isEmpty()) {
            $this->components->error('No users have consented to training.');

            return 1;
        }

        $brandTypeId = TagType::query()->where('slug', 'brand')->value('id');
        $contentTypeId = TagType::query()->where('slug', 'content')->value('id');

        $this->components->info("Found {$consentingUserIds->count()} consenting user(s), {$excludedItemIds->count()} excluded item(s).");

        $query = PhotoItem::query()
            ->join('photos', 'photos.id', '=', 'photo_items.photo_id')
            ->whereIn('photos.user_id', $consentingUserIds)
            ->whereNotIn('photo_items.item_id', $excludedItemIds)
            ->with(['tags' => function ($query) use ($excludedTagIds, $brandTypeId, $contentTypeId): void {
                $query->whereIn('tag_type_id', [$brandTypeId, $contentTypeId]);
                if ($excludedTagIds->isNotEmpty()) {
                    $query->whereNotIn('tags.id', $excludedTagIds);
                }
            }])
            ->select(['photo_items.*', 'photos.path', 'photos.user_id'])
            ->orderBy('photos.id');

        if ($since) {
            $sinceDate = Carbon::parse($since)->startOfDay();
            $query->where('photo_items.created_at', '>=', $sinceDate);
            $this->components->info("Filtering to photos tagged since {$sinceDate->toDateString()}.");
        }

        $totalRows = 0;
        $uniqueItems = [];
        $brandCount = 0;
        $contentCount = 0;

        $filename = $since
            ? 'manifest_delta_'.now()->format('Y-m-d').'.csv'
            : 'manifest_'.now()->format('Y-m-d').'.csv';

        $localPath = storage_path("app/{$filename}");

        /** @var resource $handle */
        $handle = fopen($localPath, 'w');
        fputcsv($handle, ['photo_id', 's3_key', 'item_id', 'brand_tag_ids', 'content_tag_ids']);

        $query->chunk(5000, function ($photoItems) use ($handle, &$totalRows, &$uniqueItems, &$brandCount, &$contentCount, $brandTypeId, $contentTypeId): void {
            foreach ($photoItems as $photoItem) {
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
                $uniqueItems[$photoItem->item_id] = true;

                if ($brandTagIds !== '') {
                    $brandCount++;
                }

                if ($contentTagIds !== '') {
                    $contentCount++;
                }
            }
        });

        fclose($handle);

        $s3Path = "ml/manifests/{$filename}";
        Storage::disk('s3')->put($s3Path, (string) file_get_contents($localPath));
        unlink($localPath);

        $this->newLine();
        $this->components->info('Manifest generation complete.');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total rows', number_format($totalRows)],
                ['Unique items', count($uniqueItems)],
                ['Rows with brand tags', $totalRows > 0 ? number_format($brandCount).' ('.round($brandCount / $totalRows * 100, 1).'%)' : '0'],
                ['Rows with content tags', $totalRows > 0 ? number_format($contentCount).' ('.round($contentCount / $totalRows * 100, 1).'%)' : '0'],
            ]
        );

        $this->components->success("Uploaded to S3: {$s3Path}");

        return 0;
    }
}
