<?php

namespace App\Http\Controllers\Photos;

use App\Http\Controllers\Controller;
use App\Models\PhotoItem;
use Illuminate\Http\JsonResponse;

class CopyPhotoItemController extends Controller
{
    public function __invoke(PhotoItem $photoItem): JsonResponse
    {
        $this->authorize('manage', $photoItem->photo);

        $newPhotoItem = $photoItem->replicate();
        $newPhotoItem->save();

        $newPhotoItem->tags()->sync($photoItem->tags);

        return response()->json();
    }
}
