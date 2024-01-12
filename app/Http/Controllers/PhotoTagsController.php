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
        $request->validate([
            'tag_id' => 'required|exists:tags,id',
        ]);

        if (auth()->id() !== $photo->user_id) {
            abort(404);
        }

        $photo->tags()->syncWithoutDetaching($request->tag_id);

        return response()->json();
    }

    public function destroy(Photo $photo, Tag $tag): JsonResponse
    {
        if (auth()->id() !== $photo->user_id) {
            abort(404);
        }

        $photo->tags()->detach($tag);

        return response()->json();
    }
}
