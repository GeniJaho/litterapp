<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePhotoItemRequest;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\User;
use Illuminate\Http\Request;

class PhotoItemsController extends Controller
{
    public function store(Photo $photo, Request $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $photo->items()->attach($request->item_id, [
            'picked_up' => $user->settings->picked_up_by_default,
        ]);

        return [];
    }

    public function update(PhotoItem $photoItem, UpdatePhotoItemRequest $request): array
    {
        if ($request->filled('quantity')) {
            $photoItem->quantity = $request->quantity;
        }

        if ($request->filled('picked_up')) {
            $photoItem->picked_up = $request->picked_up;
        }

        $photoItem->save();

        return [];
    }

    public function destroy(PhotoItem $photoItem)
    {
        $photoItem->delete();

        return [];
    }
}
