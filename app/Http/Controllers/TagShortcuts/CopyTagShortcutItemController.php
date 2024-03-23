<?php

namespace App\Http\Controllers\TagShortcuts;

use App\Http\Controllers\Controller;
use App\Models\TagShortcutItem;
use Illuminate\Http\JsonResponse;

class CopyTagShortcutItemController extends Controller
{
    public function __invoke(TagShortcutItem $tagShortcutItem): JsonResponse
    {
        if (auth()->id() !== (int) $tagShortcutItem->tagShortcut->user_id) {
            abort(404);
        }

        $newTagShortcutItem = $tagShortcutItem->replicate();
        $newTagShortcutItem->save();

        $newTagShortcutItem->tags()->sync($tagShortcutItem->tags);

        return response()->json();
    }
}
