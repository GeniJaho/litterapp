<?php

namespace App\Http\Controllers\Photos;

use App\DTO\BulkDeletePhotoItems;
use App\DTO\BulkPhotoItems;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\PhotoItem;
use Illuminate\Support\Facades\DB;

class BulkPhotoItemsController extends Controller
{
    public function store(BulkPhotoItems $bulkPhotoItems): void
    {
        $items = Item::query()->find(array_column($bulkPhotoItems->items, 'id'))->keyBy('id');

        DB::transaction(function () use ($items, $bulkPhotoItems): void {
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
            }
        });
    }

    public function destroy(BulkDeletePhotoItems $bulkDeletePhotoItems): void
    {
        PhotoItem::query()
            ->whereIn('photo_id', $bulkDeletePhotoItems->photo_ids)
            ->whereIn('item_id', $bulkDeletePhotoItems->item_ids)
            ->delete();
    }
}
