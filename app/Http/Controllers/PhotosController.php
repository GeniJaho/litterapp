<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;
use Inertia\Inertia;

class PhotosController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = auth()->user();

        return $user->photos()->latest()->paginate();
    }

    public function show(Photo $photo)
    {
        $photo->load('tags');

        return Inertia::render('ShowPhoto', [
            'photo' => $photo,
            'tags' => Tag::all(),
        ]);
    }
}
