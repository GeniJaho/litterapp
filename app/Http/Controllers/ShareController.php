<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\PhotoItem;
use Inertia\Inertia;
use Inertia\Response;

class ShareController extends Controller
{
    public function show(string $token): Response
    {
        $photo = Photo::query()
            ->with(['user:id,name,profile_photo_path', 'photoItems.item', 'photoItems.tags'])
            ->where('share_token', $token)
            ->firstOrFail();

        if (! $photo->isShareable()) {
            abort(403, 'This share link has expired or is invalid.');
        }

        $photo->increment('share_view_count');

        return Inertia::render('Photos/Share/Show', [
            'photo' => [
                'full_path' => $photo->full_path,
                'original_file_name' => $photo->original_file_name,
                'taken_at_local' => $photo->taken_at_local,
                'share_view_count' => $photo->share_view_count,
                'user' => $photo->user ? [
                    'name' => $photo->user->name,
                    'profile_photo_url' => $photo->user->profile_photo_url,
                ] : null,
                'photo_items' => $photo->photoItems->map(fn (PhotoItem $photoItem): array => [
                    'item' => ['name' => $photoItem->item?->name],
                    'quantity' => $photoItem->quantity,
                    'picked_up' => $photoItem->picked_up,
                    'recycled' => $photoItem->recycled,
                    'deposit' => $photoItem->deposit,
                    'tags' => $photoItem->tags->map(fn ($tag): array => [
                        'name' => $tag->name,
                    ])->all(),
                ])->all(),
            ],
        ]);
    }
}
