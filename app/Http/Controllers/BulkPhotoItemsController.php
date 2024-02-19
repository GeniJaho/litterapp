<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBulkPhotoItemRequest;
use App\Models\Item;
use Illuminate\Http\JsonResponse;

class BulkPhotoItemsController extends Controller
{
    public function store(StoreBulkPhotoItemRequest $request, Item $item): JsonResponse
    {
        $item->photos()->attach($request->photo_ids, [
            'picked_up' => $request->boolean('picked_up'),
            'recycled' => $request->boolean('recycled'),
            'deposit' => $request->boolean('deposit'),
            'quantity' => $request->integer('quantity'),
        ]);

        return response()->json();
    }
}
