<?php

namespace App\Console\Commands;

use App\Actions\Training\DistributePhotosAmongUsersAction;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\TagType;
use App\Models\User;
use App\Traits\Training\ZipsPhotos;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SelectPhotosForBrandsTraining extends Command
{
    use ZipsPhotos;

    protected $signature = 'app:select-photos-for-brands-training {--limit=1000 : Number of photos per brand}';

    protected $description = 'Zip photos of the top 50 brands from consenting users for ML training';

    public function handle(DistributePhotosAmongUsersAction $distribute): int
    {
        $limit = (int) $this->option('limit');

        $this->components->info("Selecting up to {$limit} photos per brand from users who consented to training...");

        $brandTagType = TagType::where('slug', 'brand')->first();
        $unknownBrandId = Tag::where('name', 'Brand:Unknown')->value('id');

        if ($brandTagType === null) {
            $this->components->warn('Brand tag type not found.');

            return 1;
        }

        $topBrands = Tag::query()
            ->join('photo_item_tag', 'tags.id', '=', 'photo_item_tag.tag_id')
            ->where('tags.tag_type_id', $brandTagType->id)
            ->when($unknownBrandId, fn (Builder $query) => $query->where('photo_item_tag.tag_id', '!=', $unknownBrandId))
            ->select('tags.id', 'tags.name', DB::raw('count(*) as total'))
            ->groupBy('tags.id', 'tags.name')
            ->orderByDesc('total')
            ->limit(50)
            ->get();

        $usersConsentingToTrain = User::query()->where('settings->consent_to_training', true)->pluck('id');

        $totalPhotos = 0;
        $results = [];

        foreach ($topBrands as $brand) {
            $brandName = $brand->name;
            $brandSlug = Str::slug($brandName);

            $this->components->info("Processing brand: {$brandName}");

            // Select photos from users who consented, ensuring diversity across users
            /** @var array<int, int> $userPhotoCounts */
            $userPhotoCounts = DB::table('photos')
                ->join('photo_items', 'photos.id', '=', 'photo_items.photo_id')
                ->join('photo_item_tag', 'photo_items.id', '=', 'photo_item_tag.photo_item_id')
                ->where('photo_item_tag.tag_id', $brand->id)
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
                    ->join('photo_item_tag', 'photo_items.id', '=', 'photo_item_tag.photo_item_id')
                    ->where('photo_item_tag.tag_id', $brand->id)
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
                'tag' => $brandName,
                'slug' => $brandSlug,
                'photos_count' => $photoCount,
                'photos' => $selectedPhotoPaths,
                'users_count' => $userCount,
            ];
        }

        $this->components->info("Total photos selected: {$totalPhotos}");

        $this->table(
            ['Brand', 'Photos', 'Users'],
            collect($results)->select('tag', 'photos_count', 'users_count')->toArray()
        );
        $this->newline();

        $this->zipPhotos($results, $limit, $totalPhotos, '_brands');

        return 0;
    }
}
