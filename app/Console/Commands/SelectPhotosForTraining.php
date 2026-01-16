<?php

namespace App\Console\Commands;

use App\Actions\Photos\GetItemFromPredictionAction;
use App\Models\Item;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Console\Command;
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
            $photos = Photo::query()
                ->whereIn('user_id', $usersConsentingToTrain)
                ->whereHas('items', fn ($query) => $query->where('items.id', $item->id))
                ->with('user:id,name')
                ->get();

            $photosGroupedByUser = $photos
                ->groupBy('user_id')
                ->sortByDesc(fn ($userPhotos) => $userPhotos->count());

            $totalPhotosForItem = $photos->count();
            $ratio = $totalPhotosForItem > 0 ? $limit / $totalPhotosForItem : 0;

            $selectedPhotos = collect();

            // Distribute photos across users, taking max 10 users per item
            foreach ($photosGroupedByUser->take(10) as $userPhotos) {
                $takeCount = (int) floor($userPhotos->count() * min(1.0, $ratio));

                if ($takeCount > 0) {
                    $selectedPhotos = $selectedPhotos->concat($userPhotos->take($takeCount));
                }
            }

            $userCount = $selectedPhotos->pluck('user_id')->unique()->count();
            $photoCount = $selectedPhotos->count();
            $totalPhotos += $photoCount;

            $results[] = [
                'item' => $itemName,
                'item_slug' => $itemSlug,
                'photos_count' => $photoCount,
                'photos' => $selectedPhotos->pluck('path')->all(),
                'users_count' => $userCount,
            ];
        }

        $this->components->info("Total photos selected: {$totalPhotos}");

        $this->table(
            ['Item', 'Photos', 'Users'],
            collect($results)->select(['item', 'photos_count', 'users_count'])->toArray()
        );
        $this->newline();

        $this->zipPhotos($results, $limit, $totalPhotos);

        return 0;
    }

    private function zipPhotos(array $results, int $limitPerItem, int $totalPhotos): void
    {
        $zipFilePath = "zips/photos_{$limitPerItem}_".now()->format('Y_m_d_H_i').'.zip';
        $zipFilePathOnDisk = Storage::disk(self::LOCAL_DISK)->path($zipFilePath);

        $this->components->info('Zipping images at '.$zipFilePathOnDisk);

        $bar = $this->output->createProgressBar($totalPhotos);
        $bar->start();

        $zip = new ZipArchive;

        if ($zip->open($zipFilePathOnDisk, ZipArchive::CREATE) !== true) {
            $this->components->error("Failed to create zip file: {$zipFilePathOnDisk}");
        }

        foreach ($results as $result) {

            foreach ($result['photos'] as $photoPath) {
                $zip->addFile(
                    Storage::disk('public')->path($photoPath),
                    "/{$result['item_slug']}/".basename($photoPath),
                );

                $bar->advance();
            }
        }

        $bar->finish();

        $this->newLine(2);

        $this->components->info('Finalizing zip file');

        $zip->close();

        $this->newline(2);

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
}
