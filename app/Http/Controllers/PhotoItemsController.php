<?php

namespace App\Http\Controllers;

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

    public function destroy(PhotoItem $photoItem)
    {
        $photoItem->delete();

        return [];
    }
}
