<?php

namespace App\Http\Controllers\Photos;

use App\DTO\BulkAddPhotoTags;
use App\DTO\BulkDeletePhotoItems;
use App\DTO\BulkPhotoItems;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\PhotoItemSuggestion;
use App\Models\PhotoItemTag;
use App\Models\TagShortcut;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class BulkPhotoItemsController extends Controller
{
    private function authorizeAdminOrOwnPhotos(array $photoIds): void
    {
        $user = auth()->user();
        $photoUserIds = Photo::query()->whereIn('id', $photoIds)->pluck('user_id')->unique()->values();

        if ($user->is_admin) {
            return;
        }

        if ($photoUserIds->count() === 1 && $photoUserIds->first() === $user->id) {
            return;
        }

        abort(404);
    }

    public function store(BulkPhotoItems $bulkPhotoItems): void
    {
        $this->authorizeAdminOrOwnPhotos($bulkPhotoItems->photo_ids);

        $items = Item::query()->find(array_column($bulkPhotoItems->items, 'id'))->keyBy('id');
        $usedShortcuts = TagShortcut::query()->find($bulkPhotoItems->used_shortcuts)->keyBy('id');

        DB::transaction(function () use ($items, $usedShortcuts, $bulkPhotoItems): void {
            foreach ($bulkPhotoItems->items as $requestItem) {
                /** @var Item $item */
                $item = $items[$requestItem->id];

                foreach ($bulkPhotoItems->photo_ids as $photoId) {
                    /** @var PhotoItem $photoItem */
                    $photoItem = PhotoItem::query()->create([
                        'photo_id' => $photoId,
                        'item_id' => $item->id,
                        'picked_up' => $requestItem->picked_up,
                        'recycled' => $requestItem->recycled,
                        'deposit' => $requestItem->deposit,
                        'quantity' => $requestItem->quantity,
                    ]);

                    $photoItem->tags()->attach($requestItem->tag_ids);
                }

                PhotoItemSuggestion::query()
                    ->where('item_id', $item->id)
                    ->whereIn('photo_id', $bulkPhotoItems->photo_ids)
                    ->whereNull('is_accepted')
                    ->update(['is_accepted' => true]);
            }

            foreach ($bulkPhotoItems->used_shortcuts as $shortcutId) {
                /** @var TagShortcut $tagShortcut */
                $tagShortcut = $usedShortcuts[$shortcutId];

                $tagShortcut->increment('used_times', count($bulkPhotoItems->photo_ids));
            }
        });
    }

    public function destroy(BulkDeletePhotoItems $bulkDeletePhotoItems): void
    {
        $this->authorizeAdminOrOwnPhotos($bulkDeletePhotoItems->photo_ids);

        DB::transaction(function () use ($bulkDeletePhotoItems): void {
            PhotoItem::query()
                ->whereIn('photo_id', $bulkDeletePhotoItems->photo_ids)
                ->whereIn('item_id', $bulkDeletePhotoItems->item_ids)
                ->delete();

            $photoItems = PhotoItem::query()
                ->whereIn('photo_id', $bulkDeletePhotoItems->photo_ids)
                ->pluck('id');

            PhotoItemTag::query()
                ->whereIn('photo_item_id', $photoItems)
                ->whereIn('tag_id', $bulkDeletePhotoItems->tag_ids)
                ->delete();
        });
    }

    public function addTags(BulkAddPhotoTags $bulkAddPhotoTags): RedirectResponse
    {
        $this->authorizeAdminOrOwnPhotos($bulkAddPhotoTags->photo_ids);

        $photosWithMultipleItems = [];
        $photosWithNoItems = [];
        $tagsAdded = false;

        DB::transaction(function () use ($bulkAddPhotoTags, &$photosWithMultipleItems, &$photosWithNoItems, &$tagsAdded): void {
            $photoItemCounts = PhotoItem::query()
                ->whereIn('photo_id', $bulkAddPhotoTags->photo_ids)
                ->select('photo_id', DB::raw('count(*) as item_count'))
                ->groupBy('photo_id')
                ->pluck('item_count', 'photo_id');

            /** @var array<int, int> $counts */
            $counts = $photoItemCounts->toArray();

            $photosWithNoItems = array_values(array_diff($bulkAddPhotoTags->photo_ids, $photoItemCounts->keys()->all()));
            $photosWithMultipleItems = array_keys(array_filter($counts, fn (int $count): bool => $count > 1));

            $photosWithSingleItem = array_keys(array_filter($counts, fn (int $count): bool => $count === 1));

            if ($photosWithSingleItem === []) {
                return;
            }

            $photoItems = PhotoItem::query()
                ->whereIn('photo_id', $photosWithSingleItem)
                ->get();

            foreach ($photoItems as $photoItem) {
                $existingTagIds = $photoItem->tags()->pluck('tags.id')->all();

                $tagsToAttach = array_diff($bulkAddPhotoTags->tag_ids, $existingTagIds);

                if ($tagsToAttach !== []) {
                    $photoItem->tags()->attach($tagsToAttach);

                    $tagsAdded = true;
                }
            }
        });

        return back()->with('bulkAddTagsResult', [
            'photos_with_no_items' => $photosWithNoItems,
            'photos_with_multiple_items' => $photosWithMultipleItems,
            'tags_added' => $tagsAdded,
        ]);
    }
}
