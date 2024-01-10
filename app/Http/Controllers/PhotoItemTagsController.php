<?php

namespace App\Http\Controllers;

use App\Models\PhotoItem;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhotoItemTagsController extends Controller
{
    public function store(PhotoItem $photoItem, Request $request): JsonResponse
    {
        $photoItem->tags()->syncWithoutDetaching($request->input('tag_id'));

        return response()->json();
    }

    public function destroy(PhotoItem $photoItem, Tag $tag): JsonResponse
    {
        $photoItem->tags()->detach($tag);

        return response()->json();
    }
}
