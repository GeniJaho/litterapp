<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePhotoItemTagRequest;
use App\Models\PhotoItem;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;

class PhotoItemTagsController extends Controller
{
    public function store(PhotoItem $photoItem, StorePhotoItemTagRequest $request): JsonResponse
    {
        if (auth()->id() !== $photoItem->photo->user_id) {
            abort(404);
        }

        $photoItem->tags()->syncWithoutDetaching($request->input('tag_ids'));

        return response()->json();
    }

    public function destroy(PhotoItem $photoItem, Tag $tag): JsonResponse
    {
        if (auth()->id() !== $photoItem->photo->user_id) {
            abort(404);
        }

        $photoItem->tags()->detach($tag);

        return response()->json();
    }
}
