<?php

namespace App\Console\Commands;

use App\Actions\Photos\GetItemFromPredictionAction;
use App\Models\Item;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class SelectPhotosForTraining extends Command
{
    protected $signature = 'app:select-photos-for-training {--limit=1000 : Number of photos per item}';

    protected $description = 'Start photos from consenting users for ML training';

    private const LOCAL_DISK = 'local';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $this->components->info("Selecting up to {$limit} photos per item from users who consented to training...");

        // Preload all items at once to avoid N+1 queries
        $items = Item::whereIn('name', GetItemFromPredictionAction::ITEM_CLASS_NAMES)->get()->keyBy('name');
        $usersConsentingToTrain = User::query()->where('settings->consent_to_training', true)->pluck('id');

        $totalPhotos = 0;
        $results = [];

        foreach (GetItemFromPredictionAction::ITEM_CLASS_NAMES as $itemSlug => $itemName) {
            $item = $items->get($itemName);

            if (! $item) {
                $this->components->warn("Item not found: {$itemName}");

                continue;
            }

            $this->components->info("Processing item: {$itemName}");

            // Select photos from users who consented, ensuring diversity across users
            /** @var array<int, int> $userPhotoCounts */
            $userPhotoCounts = DB::table('photos')
                ->join('photo_items', 'photos.id', '=', 'photo_items.photo_id')
                ->where('photo_items.item_id', $item->id)
                ->whereIn('photos.user_id', $usersConsentingToTrain)
                ->select('photos.user_id', DB::raw('count(*) as total'))
                ->groupBy('photos.user_id')
                ->orderByDesc('total')
                ->pluck('total', 'user_id')
                ->all();

            $takeCounts = $this->distributeFairly($userPhotoCounts, $limit);

            $selectedPhotoPaths = [];
            $selectedUserIds = [];

            foreach ($takeCounts as $userId => $takeCount) {
                if ($takeCount <= 0) {
                    continue;
                }

                $paths = Photo::query()
                    ->join('photo_items', 'photos.id', '=', 'photo_items.photo_id')
                    ->where('photo_items.item_id', $item->id)
                    ->where('photos.user_id', $userId)
                    ->limit($takeCount)
                    ->pluck('photos.path')
                    ->all();

                $selectedPhotoPaths = array_merge($selectedPhotoPaths, $paths);
                if (count($paths) > 0) {
                    $selectedUserIds[] = $userId;
                }
            }

            $photoCount = count($selectedPhotoPaths);
            $userCount = count(array_unique($selectedUserIds));
            $totalPhotos += $photoCount;

            $results[] = [
                'item' => $itemName,
                'item_slug' => $itemSlug,
                'photos_count' => $photoCount,
                'photos' => $selectedPhotoPaths,
                'users_count' => $userCount,
            ];
        }

        $this->components->info("Total photos selected: {$totalPhotos}");

        $this->table(
            ['Item', 'Photos', 'Users'],
            collect($results)->select('item', 'photos_count', 'users_count')->toArray()
        );
        $this->newline();

        $this->zipPhotos($results, $limit, $totalPhotos);

        return 0;
    }

    /**
     * @phpstan-ignore-next-line
     */
    private function zipPhotos(array $results, int $limitPerItem, int $totalPhotos): void
    {
        $zipFilePath = "zips/photos_{$limitPerItem}_".now()->format('Y_m_d_H_i').'.zip';
        $zipFilePathOnDisk = Storage::disk(self::LOCAL_DISK)->path($zipFilePath);

        if (! Storage::disk(self::LOCAL_DISK)->exists('zips')) {
            Storage::disk(self::LOCAL_DISK)->makeDirectory('zips');
        }

        $this->components->info('Zipping images at '.$zipFilePathOnDisk);

        $zip = new ZipArchive;

        if ($zip->open($zipFilePathOnDisk, ZipArchive::CREATE) !== true) {
            $this->components->error("Failed to create zip file: {$zipFilePathOnDisk}");

            return;
        }

        $bar = $this->output->createProgressBar($totalPhotos);
        $bar->start();

        foreach ($results as $result) {

            foreach ($result['photos'] as $photoPath) {
                $zip->addFile(
                    Storage::disk('public')->path($photoPath),
                    "/{$result['item_slug']}/".basename((string) $photoPath),
                );

                $bar->advance();
            }
        }

        $bar->finish();

        $this->newLine(2);

        $this->components->info('Finalizing zip file');

        $zip->close();

        $this->components->info(sprintf(
            'Peak memory used: [%s]',
            $this->formatStorageSize(memory_get_peak_usage(true))
        ));
        if (Storage::disk(self::LOCAL_DISK)->exists($zipFilePath)) {
            $this->components->info(sprintf(
                'Zip file size: [%s]',
                $this->formatStorageSize(Storage::disk(self::LOCAL_DISK)->size($zipFilePath))
            ));

            $this->components->info('Uploading zip file to S3...');

            $this->uploadZipFileToS3($zipFilePath);
        }

        $this->components->success('Done!');
    }

    protected function formatStorageSize(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= 1024 ** $pow;

        return round($bytes, $precision).$units[$pow];
    }

    private function uploadZipFileToS3(string $zipFilePath): void
    {
        $uploadResult = Storage::disk('s3')->putFile(
            $zipFilePath,
            Storage::disk(self::LOCAL_DISK)->path($zipFilePath),
        );

        if ($uploadResult) {
            Storage::disk(self::LOCAL_DISK)->delete($zipFilePath);
        } else {
            $this->components->error('Failed to upload zip file to S3.');
        }
    }

    /**
     * @param  array<int, int>  $userCounts  userId => photoCount
     * @return array<int, int> userId => takeCount
     */
    private function distributeFairly(array $userCounts, int $limit): array
    {
        $totalAvailable = array_sum($userCounts);
        if ($totalAvailable <= $limit) {
            return $userCounts;
        }

        if ($totalAvailable === 0) {
            return [];
        }

        $ratio = $limit / $totalAvailable;
        $takeCounts = [];
        $remainders = [];

        foreach ($userCounts as $userId => $count) {
            $exact = $count * $ratio;
            $takeCounts[$userId] = (int) floor($exact);
            $remainders[$userId] = $exact - $takeCounts[$userId];
        }

        $currentTotal = array_sum($takeCounts);
        $diff = $limit - $currentTotal;

        if ($diff > 0) {
            arsort($remainders);
            foreach (array_keys($remainders) as $userId) {
                if ($diff <= 0) {
                    break;
                }

                if ($takeCounts[$userId] < $userCounts[$userId]) {
                    $takeCounts[$userId]++;
                    $diff--;
                }
            }
        }

        return $takeCounts;
    }
}
