<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Inertia\Inertia;
use Inertia\Response;

class ShareController extends Controller
{
    public function show(string $token): Response
    {
        $photo = Photo::query()
            ->with(['user', 'photoItems.item', 'photoItems.tags'])
            ->where('share_token', $token)
            ->firstOrFail();

        if (! $photo->isShareable()) {
            abort(403, 'This share link has expired or is invalid.');
        }

        $photo->incrementShareViewCount();

        return Inertia::render('Photos/Share/Show', [
            'photo' => $photo,
        ]);
    }
}
