<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PhotoItem;
use App\Models\PhotoItemTag;
use App\Models\Photo;
use App\Models\Tag;
use Illuminate\Http\Request;

class PhotoItemTagsController extends Controller
{
    public function store(Photo $photo, Item $item, Request $request): array
    {
        PhotoItemTag::query()->firstOrCreate([
                'item_id' => $item->id,
                'photo_id' => $photo->id,
                'tag_id' => $request->input('tag_id'),
            ]);

        return [];
    }

    public function destroy(Photo $photo, Item $item, Tag $tag): array
    {
        PhotoItemTag::query()
            ->where('item_id', $item->id)
            ->where('photo_id', $photo->id)
            ->where('tag_id', $tag->id)
            ->delete();

        return [];
    }
}
