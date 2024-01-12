<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePhotoItemRequest;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhotoItemsController extends Controller
{
    // todo validate the item_id exists
    public function store(Photo $photo, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $photo->items()->attach($request->item_id, [
            'picked_up' => $user->settings->picked_up_by_default,
            'recycled' => $user->settings->recycled_by_default,
        ]);

        return response()->json();
    }

    public function update(PhotoItem $photoItem, UpdatePhotoItemRequest $request): JsonResponse
    {
        if ($request->filled('quantity')) {
            $photoItem->quantity = $request->quantity;
        }

        if ($request->filled('picked_up')) {
            $photoItem->picked_up = $request->picked_up;
        }

        if ($request->filled('recycled')) {
            $photoItem->recycled = $request->recycled;
        }

        $photoItem->save();

        return response()->json();
    }

    public function destroy(PhotoItem $photoItem): JsonResponse
    {
        $photoItem->delete();

        return response()->json();
    }
}
