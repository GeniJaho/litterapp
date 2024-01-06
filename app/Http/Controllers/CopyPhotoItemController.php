<?php

namespace App\Http\Controllers;

use App\Models\PhotoItem;

class CopyPhotoItemController extends Controller
{
    public function __invoke(PhotoItem $photoItem): array
    {
        $newPhotoItem = $photoItem->replicate();
        $newPhotoItem->save();

        $newPhotoItem->tags()->sync($photoItem->tags);

        return [];
    }
}
