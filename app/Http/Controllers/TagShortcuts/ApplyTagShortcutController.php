<?php

namespace App\Http\Controllers\TagShortcuts;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\TagShortcut;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ApplyTagShortcutController extends Controller
{
    public function __invoke(Photo $photo, TagShortcut $tagShortcut): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->id !== $photo->user_id) {
            abort(404);
        }

        if ($user->id !== $tagShortcut->user_id) {
            abort(404);
        }

        $tagShortcut->loadMissing('tagShortcutItems.tagShortcutItemTags');

        DB::transaction(function () use ($tagShortcut, $photo) {
            foreach ($tagShortcut->tagShortcutItems as $shortcut) {
                /** @var PhotoItem $photoItem */
                $photoItem = PhotoItem::query()->create([
                    'photo_id' => $photo->id,
                    'item_id' => $shortcut->item_id,
                    'picked_up' => $shortcut->picked_up,
                    'recycled' => $shortcut->recycled,
                    'deposit' => $shortcut->deposit,
                    'quantity' => $shortcut->quantity,
                ]);

                $photoItem->tags()->attach($shortcut->tagShortcutItemTags->pluck('tag_id'));
            }
        });

        return response()->json();
    }
}
