<?php

namespace App\Http\Controllers;

use App\Models\PhotoItem;
use Illuminate\Http\JsonResponse;

class CopyPhotoItemController extends Controller
{
    public function __invoke(PhotoItem $photoItem): JsonResponse
    {
        $newPhotoItem = $photoItem->replicate();
        $newPhotoItem->save();

        $newPhotoItem->tags()->sync($photoItem->tags);

        return response()->json();
    }
}
