<?php

namespace App\Http\Controllers\TagShortcuts;

use App\Http\Controllers\Controller;
use App\Http\Requests\TagShortcuts\StoreTagShortcutItemTagRequest;
use App\Models\Tag;
use App\Models\TagShortcutItem;
use Illuminate\Http\JsonResponse;

class TagShortcutItemTagsController extends Controller
{
    public function store(TagShortcutItem $tagShortcutItem, StoreTagShortcutItemTagRequest $request): JsonResponse
    {
        if (auth()->id() !== $tagShortcutItem->tagShortcut->user_id) {
            abort(404);
        }

        $tagShortcutItem->tags()->syncWithoutDetaching($request->input('tag_ids'));

        return response()->json();
    }

    public function destroy(TagShortcutItem $tagShortcutItem, Tag $tag): JsonResponse
    {
        if (auth()->id() !== $tagShortcutItem->tagShortcut->user_id) {
            abort(404);
        }

        $tagShortcutItem->tags()->detach($tag);

        return response()->json();
    }
}
