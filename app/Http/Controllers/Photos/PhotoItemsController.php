<?php

namespace App\Http\Controllers\Photos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Photos\StorePhotoItemRequest;
use App\Http\Requests\Photos\UpdatePhotoItemRequest;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PhotoItemsController extends Controller
{
    public function store(Photo $photo, StorePhotoItemRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->id !== $photo->user_id) {
            abort(404);
        }

        $photo->items()->attach($request->item_ids, [
            'picked_up' => $user->settings->picked_up_by_default,
            'recycled' => $user->settings->recycled_by_default,
            'deposit' => $user->settings->deposit_by_default,
        ]);

        return response()->json();
    }

    public function update(PhotoItem $photoItem, UpdatePhotoItemRequest $request): JsonResponse
    {
        if (auth()->id() !== $photoItem->photo->user_id) {
            abort(404);
        }

        if ($request->filled('quantity')) {
            $photoItem->quantity = $request->integer('quantity');
        }

        if ($request->filled('picked_up')) {
            $photoItem->picked_up = $request->boolean('picked_up');
        }

        if ($request->filled('recycled')) {
            $photoItem->recycled = $request->boolean('recycled');
        }

        if ($request->filled('deposit')) {
            $photoItem->deposit = $request->boolean('deposit');
        }

        $photoItem->save();

        return response()->json();
    }

    public function destroy(PhotoItem $photoItem): JsonResponse
    {
        if (auth()->id() !== $photoItem->photo->user_id) {
            abort(404);
        }

        $photoItem->delete();

        return response()->json();
    }
}
