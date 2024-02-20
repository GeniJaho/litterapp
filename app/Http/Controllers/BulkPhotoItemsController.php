<?php

namespace App\Http\Controllers;

use App\DTO\BulkPhotoItems;
use App\Models\Item;
use Illuminate\Http\JsonResponse;

class BulkPhotoItemsController extends Controller
{
    public function store(BulkPhotoItems $bulkPhotoItems): JsonResponse
    {
        foreach ($bulkPhotoItems->items as $item) {
            $dbItem = Item::find($item->id);

            $dbItem->photos()->attach($bulkPhotoItems->photo_ids, [
                'picked_up' => $item->picked_up,
                'recycled' => $item->recycled,
                'deposit' => $item->deposit,
                'quantity' => $item->quantity,
            ]);
        }

        return response()->json();
    }
}
