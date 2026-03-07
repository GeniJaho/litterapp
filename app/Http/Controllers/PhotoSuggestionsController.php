<?php

namespace App\Http\Controllers;

use App\Models\PhotoSuggestion;
use Illuminate\Http\JsonResponse;

class PhotoSuggestionsController extends Controller
{
    public function reject(PhotoSuggestion $photoSuggestion): JsonResponse
    {
        if (auth()->id() !== $photoSuggestion->photo->user_id) {
            abort(404);
        }

        $photoSuggestion->update(['is_accepted' => false]);

        return response()->json();
    }
}
