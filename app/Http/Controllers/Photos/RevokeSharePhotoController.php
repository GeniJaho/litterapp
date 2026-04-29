<?php

namespace App\Http\Controllers\Photos;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use Illuminate\Http\JsonResponse;

class RevokeSharePhotoController extends Controller
{
    public function __invoke(Photo $photo): JsonResponse
    {
        $this->authorize('manage', $photo);

        $photo->update([
            'share_token' => null,
            'share_expires_at' => null,
        ]);

        return response()->json(['message' => 'Share link revoked.']);
    }
}
