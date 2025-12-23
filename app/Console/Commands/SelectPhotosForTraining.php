<?php

namespace App\Console\Commands;

use App\Actions\Photos\GetItemFromPredictionAction;
use App\Models\Item;
use App\Models\Photo;
use Illuminate\Console\Command;

class SelectPhotosForTraining extends Command
{

    protected $signature = 'app:select-photos-for-training {--limit=1000 : Number of photos per item}';

    protected $description = 'Start photos from consenting users for ML training';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $this->info("Selecting up to {$limit} photos per item from users who consented to training...");
        $this->newLine();

        // Preload all items at once to avoid N+1 queries
        $items = Item::whereIn('name', GetItemFromPredictionAction::ITEM_CLASS_NAMES)->get()->keyBy('name');

        $totalPhotos = 0;
        $results = [];
        $maxPhotosPerUser = (int) ceil($limit / 2);

        foreach (GetItemFromPredictionAction::ITEM_CLASS_NAMES as $itemName) {
            $item = $items->get($itemName);

            if (! $item) {
                $this->warn("Item not found: {$itemName}");

                continue;
            }

            // Select photos from users who consented, ensuring diversity across users
            // Limit to max 50% of photos from any single user
            $selectedPhotos = collect();

            $photosGroupedByUser = Photo::query()
                ->whereHas('user', function ($query) {
                    $query->whereRaw("JSON_EXTRACT(settings, '$.consent_to_training') = true");
                })
                ->whereHas('items', function ($query) use ($item) {
                    $query->where('items.id', $item->id);
                })
                ->with('user:id,name')
                ->get()
                ->groupBy('user_id');

            // Distribute photos across users, taking max 50% from any single user
            foreach ($photosGroupedByUser as $userPhotos) {
                $takeCount = min($maxPhotosPerUser, $limit - $selectedPhotos->count());

                if ($takeCount <= 0) {
                    break;
                }

                $selectedPhotos = $selectedPhotos->concat($userPhotos->take($takeCount));
            }

            $userCount = $selectedPhotos->pluck('user_id')->unique()->count();
            $photoCount = $selectedPhotos->count();
            $totalPhotos += $photoCount;

            $results[] = [
                'item' => $itemName,
                'photos' => $photoCount,
                'users' => $userCount,
            ];

            $this->line("✓ {$itemName}: {$photoCount} photos from {$userCount} users");
        }

        $this->newLine();
        $this->info("Total photos selected: {$totalPhotos}");
        $this->newLine();

        // Display summary table
        $this->table(
            ['Item', 'Photos', 'Users'],
            $results
        );

        return 0;
    }
}
