<?php

namespace App\Http\Controllers\Photos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Photos\StorePhotoItemTagRequest;
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

        $photoItem->tags()->syncWithoutDetaching((array) $request->input('tag_ids'));

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
