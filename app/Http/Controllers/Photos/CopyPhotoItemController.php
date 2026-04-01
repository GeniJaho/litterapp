<?php

namespace App\Http\Controllers\Photos;

use App\Http\Controllers\Controller;
use App\Models\PhotoItem;
use Illuminate\Http\JsonResponse;

class CopyPhotoItemController extends Controller
{
    public function __invoke(PhotoItem $photoItem): JsonResponse
    {
        $user = auth()->user();
        $photo = $photoItem->photo;

        if (! $user->is_admin && $user->id !== $photo->user_id) {
            abort(404);
        }

        $newPhotoItem = $photoItem->replicate();
        $newPhotoItem->save();

        $newPhotoItem->tags()->sync($photoItem->tags);

        return response()->json();
    }
}
