<?php

namespace App\Http\Controllers\Photos;

use App\Actions\Photos\GenerateShareTokenAction;
use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SharePhotoController extends Controller
{
    public function __invoke(Request $request, Photo $photo, GenerateShareTokenAction $generateShareToken): JsonResponse
    {
        if (auth()->id() !== $photo->user_id) {
            abort(404);
        }

        $validated = $request->validate([
            'expires_in' => ['nullable', 'integer', 'in:7,30,90'],
        ]);

        $expiresInDays = $validated['expires_in'] ?? null;

        $generateShareToken->run($photo, $expiresInDays);

        return response()->json([
            'share_url' => route('photo.share', ['token' => $photo->share_token]),
            'share_token' => $photo->share_token,
            'share_expires_at' => $photo->share_expires_at?->toIso8601String(),
        ]);
    }
}
