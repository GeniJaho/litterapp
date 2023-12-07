<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePhotosRequest;
use App\Models\Photo;

class UploadPhotosController extends Controller
{
    public function store(StorePhotosRequest $request)
    {
        $user = auth()->user();

        $photo = $request->file('photo');

        $originalFileName = $photo->getClientOriginalName();

        Photo::create([
            'path' => $photo->storeAs('photos', $originalFileName, 'public'),
            'user_id' => $user->id,
        ]);

        return [];
    }
}
