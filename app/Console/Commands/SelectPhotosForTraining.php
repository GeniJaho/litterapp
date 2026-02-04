<?php

namespace App\Console\Commands;

use App\Actions\Photos\GetItemFromPredictionAction;
use App\Actions\Training\DistributePhotosAmongUsersAction;
use App\Models\Item;
use App\Models\Photo;
use App\Models\User;
use App\Traits\Training\ZipsPhotos;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SelectPhotosForTraining extends Command
{
    use ZipsPhotos;

    protected $signature = 'app:select-photos-for-training {--limit=1000 : Number of photos per item}';

    protected $description = 'Start photos from consenting users for ML training';

    public function handle(DistributePhotosAmongUsersAction $distribute): int
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
                ->where('photos.size_kb', '<=', 300)
                ->select('photos.user_id', DB::raw('count(*) as total'))
                ->groupBy('photos.user_id')
                ->orderByDesc('total')
                ->pluck('total', 'user_id')
                ->all();

            $takeCounts = $distribute->run($userPhotoCounts, $limit);

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
                    ->where('photos.size_kb', '<=', 300)
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
                'slug' => $itemSlug,
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
}
