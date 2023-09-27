<?php

namespace App\Http\Controllers;

use App\Models\PhotoItem;
use App\Models\Tag;
use Illuminate\Http\Request;

class PhotoItemTagsController extends Controller
{
    public function store(PhotoItem $photoItem, Request $request)
    {
        $photoItem->tags()->syncWithoutDetaching($request->input('tag_id'));

        return [];
    }

    public function destroy(PhotoItem $photoItem, Tag $tag)
    {
        $photoItem->tags()->detach($tag);

        return [];
    }
}
