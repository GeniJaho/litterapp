<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Request;

class PhotoTagsController extends Controller
{
    public function store(Photo $photo, Request $request)
    {
        $photo->tags()->syncWithoutDetaching($request->tag_id);

        return [];
    }
}
