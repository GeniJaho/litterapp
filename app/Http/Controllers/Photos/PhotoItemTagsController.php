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
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (! $user) {
            abort(404);
        }

        $photo = $photoItem->photo;

        if (! $user->is_admin && $user->id !== $photo->user_id) {
            abort(404);
        }

        $photoItem->tags()->syncWithoutDetaching((array) $request->input('tag_ids'));

        return response()->json();
    }

    public function destroy(PhotoItem $photoItem, Tag $tag): JsonResponse
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (! $user) {
            abort(404);
        }

        $photo = $photoItem->photo;

        if (! $user->is_admin && $user->id !== $photo->user_id) {
            abort(404);
        }

        $photoItem->tags()->detach($tag);

        return response()->json();
    }
}
