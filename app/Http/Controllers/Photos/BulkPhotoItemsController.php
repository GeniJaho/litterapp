<?php

namespace App\Http\Controllers\Photos;

use App\DTO\BulkDeletePhotoItems;
use App\DTO\BulkPhotoItems;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\PhotoItem;
use App\Models\PhotoItemSuggestion;
use App\Models\PhotoItemTag;
use App\Models\TagShortcut;
use Illuminate\Support\Facades\DB;

class BulkPhotoItemsController extends Controller
{
    public function store(BulkPhotoItems $bulkPhotoItems): void
    {
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
}
