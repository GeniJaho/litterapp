<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhotoTagsController extends Controller
{
    public function store(Photo $photo, Request $request): JsonResponse
    {
        $photo->tags()->syncWithoutDetaching($request->tag_id);

        return response()->json();
    }

    public function destroy(Photo $photo, Tag $tag): JsonResponse
    {
        $photo->tags()->detach($tag);

        return response()->json();
    }
}
