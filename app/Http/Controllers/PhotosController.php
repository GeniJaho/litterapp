<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PhotosController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = auth()->user();

        return $user->photos()->paginate();
    }
}
