<?php

namespace App\Http\Controllers;

use App\Actions\Photos\GetRelevantTagShortcutAction;
use App\Actions\Photos\SuggestsPhotoTags;
use App\DTO\PhotoSuggestionResult;
use App\Models\Photo;
use App\Models\PhotoSuggestion;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;

class LitterBotController extends Controller
{
    public function suggest(
        Photo $photo,
        SuggestsPhotoTags $action,
        GetRelevantTagShortcutAction $getRelevantTagShortcutAction,
        #[CurrentUser] User $user
    ): JsonResponse {
        if (! $user->settings->litterbot_enabled) {
            return response()->json();
        }

        if ($photo->user_id !== $user->id) {
            abort(404);
        }

        $existingSuggestion = $photo->photoSuggestions()->with(['item', 'brandTag', 'contentTag'])->first();

        if ($existingSuggestion instanceof PhotoSuggestion) {
            return response()->json([
                'suggestion' => $existingSuggestion,
                'shortcut' => $getRelevantTagShortcutAction->run($user, $existingSuggestion->item_id),
            ]);
        }

        $result = $action->run($photo);

        if (! $result instanceof PhotoSuggestionResult) {
            return response()->json([
                'error' => 'Failed to connect to LitterBot service',
            ], 422);
        }

        $attributes = $result->toSuggestionAttributes();

        if ($attributes === null) {
            return response()->json();
        }

        if ($photo->items()->where('item_id', $attributes['item_id'])->exists()) {
            return response()->json();
        }

        $suggestion = $photo->photoSuggestions()->create($attributes)->load(['item', 'brandTag', 'contentTag']);

        return response()->json([
            'suggestion' => $suggestion,
            'shortcut' => $getRelevantTagShortcutAction->run($user, $attributes['item_id']),
        ]);
    }
}
