<?php

namespace App\Http\Controllers;

use App\Models\PhotoItemSuggestion;
use Illuminate\Http\JsonResponse;

class PhotoItemSuggestionsController extends Controller
{
    public function reject(PhotoItemSuggestion $photoItemSuggestion): JsonResponse
    {
        if (auth()->id() !== $photoItemSuggestion->photo->user_id) {
            abort(404);
        }

        $photoItemSuggestion->update(['is_accepted' => false]);

        return response()->json();
    }
}
