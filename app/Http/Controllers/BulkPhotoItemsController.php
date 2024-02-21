<?php

namespace App\Http\Controllers;

use App\DTO\BulkPhotoItems;
use App\Models\Item;
use Illuminate\Http\JsonResponse;

class BulkPhotoItemsController extends Controller
{
    public function store(BulkPhotoItems $bulkPhotoItems): JsonResponse
    {
        $items = Item::query()->find(array_column($bulkPhotoItems->items, 'id'))->keyBy('id');

        foreach ($bulkPhotoItems->items as $requestItem) {
            /** @var Item $item */
            $item = $items[$requestItem->id];

            $item->photos()->attach($bulkPhotoItems->photo_ids, [
                'picked_up' => $requestItem->picked_up,
                'recycled' => $requestItem->recycled,
                'deposit' => $requestItem->deposit,
                'quantity' => $requestItem->quantity,
            ]);
        }

        return response()->json();
    }
}
