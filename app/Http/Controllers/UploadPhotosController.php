<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Request;

class UploadPhotosController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();
        $originalFileName = $request->file('photo')->getClientOriginalName();

        Photo::create([
            'path' => $request->file('photo')->storeAs('photos', $originalFileName, 'public'),
            'user_id' => $user->id,
        ]);
    }
}
