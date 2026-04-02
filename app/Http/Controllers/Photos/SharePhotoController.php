<?php

namespace App\Http\Controllers\Photos;

use App\Actions\Photos\GenerateShareTokenAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Photos\SharePhotoRequest;
use App\Models\Photo;
use Illuminate\Http\JsonResponse;

class SharePhotoController extends Controller
{
    public function __invoke(SharePhotoRequest $request, Photo $photo, GenerateShareTokenAction $generateShareToken): JsonResponse
    {
        if (auth()->id() !== $photo->user_id) {
            abort(404);
        }

        $generateShareToken->run($photo, $request->validated('expires_in'));

        return response()->json([
            'share_url' => route('photo.share', ['token' => $photo->share_token]),
            'share_token' => $photo->share_token,
            'share_expires_at' => $photo->share_expires_at?->toIso8601String(),
        ]);
    }
}
