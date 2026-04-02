<?php

namespace App\Http\Controllers\Photos;

use App\Actions\Photos\GenerateShareTokenAction;
use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\JsonResponse;

class SharePhotoController extends Controller
{
    public function __invoke(Photo $photo, GenerateShareTokenAction $generateShareToken): JsonResponse
    {
        if (auth()->id() !== $photo->user_id) {
            abort(404);
        }

        if (! $photo->share_token) {
            $generateShareToken->run($photo);
        }

        return response()->json([
            'share_url' => route('photo.share', ['token' => $photo->share_token]),
            'share_token' => $photo->share_token,
        ]);
    }
}
