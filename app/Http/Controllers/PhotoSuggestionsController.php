<?php

namespace App\Http\Controllers;

use App\Models\PhotoSuggestion;
use Illuminate\Http\JsonResponse;

class PhotoSuggestionsController extends Controller
{
    public function reject(PhotoSuggestion $photoSuggestion): JsonResponse
    {
        $this->authorize('manage', $photoSuggestion->photo);

        $photoSuggestion->update(['is_accepted' => false]);

        return response()->json();
    }
}
