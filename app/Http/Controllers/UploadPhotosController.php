<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Request;

class UploadPhotosController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();

        $photos = $request->file('photos');

        foreach ($photos as $photo) {
            $originalFileName = $photo->getClientOriginalName();

            Photo::create([
                'path' => $photo->storeAs('photos', $originalFileName, 'public'),
                'user_id' => $user->id,
            ]);
        }
    }
}
