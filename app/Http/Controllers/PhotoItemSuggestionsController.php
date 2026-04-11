<?php

namespace App\Http\Controllers;

use App\Models\PhotoItemSuggestion;
use Illuminate\Http\JsonResponse;

class PhotoItemSuggestionsController extends Controller
{
    public function reject(PhotoItemSuggestion $photoItemSuggestion): JsonResponse
    {
        $this->authorize('manage', $photoItemSuggestion->photo);

        $photoItemSuggestion->update(['is_accepted' => false]);

        return response()->json();
    }
}
