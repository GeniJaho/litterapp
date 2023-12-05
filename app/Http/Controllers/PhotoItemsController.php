<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Photo;
use Illuminate\Http\Request;

class PhotoItemsController extends Controller
{
    public function store(Photo $photo, Request $request)
    {
        $photo->items()->syncWithoutDetaching($request->item_id);

        return [];
    }

    public function destroy(Photo $photo, Item $item)
    {
        $photo->items()->detach($item);

        return [];
    }
}
