<?php

namespace App\Http\Controllers;

use App\Models\PhotoItem;

class PhotoItemPickedUpController extends Controller
{
    public function store(PhotoItem $photoItem)
    {
        $photoItem->update([
            'picked_up' => ! $photoItem->picked_up,
        ]);

        return [];
    }
}
