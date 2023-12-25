<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use Illuminate\Http\Request;

class PhotoItemsController extends Controller
{
    public function store(Photo $photo, Request $request)
    {
        $photo->items()->attach($request->item_id);

        return [];
    }

    public function destroy(PhotoItem $photoItem)
    {
        $photoItem->delete();

        return [];
    }
}
