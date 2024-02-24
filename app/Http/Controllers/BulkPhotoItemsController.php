<?php

namespace App\Http\Controllers;

use App\DTO\BulkPhotoItems;
use App\Models\Item;
use App\Models\PhotoItem;
use Illuminate\Support\Facades\DB;

class BulkPhotoItemsController extends Controller
{
    public function store(BulkPhotoItems $bulkPhotoItems): void
    {
        $items = Item::query()->find(array_column($bulkPhotoItems->items, 'id'))->keyBy('id');

        DB::transaction(function () use ($items, $bulkPhotoItems) {
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
}
