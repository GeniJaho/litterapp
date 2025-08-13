<?php

namespace App\Http\Controllers;

use App\Actions\Photos\ClassifiesPhoto;
use App\Actions\Photos\GetItemFromPredictionAction;
use App\DTO\PhotoItemPrediction;
use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItemSuggestion;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;

class LitterBotController extends Controller
{
    public function suggest(
        Photo $photo,
        ClassifiesPhoto $action,
        GetItemFromPredictionAction $getItemFromPredictionAction,
        #[CurrentUser] User $user
    ): JsonResponse {
        if (! $user->settings->litterbot_enabled) {
            return response()->json();
        }

        if ($photo->user_id !== $user->id) {
            abort(404);
        }

        $existingSuggestion = $photo->photoItemSuggestions()->with('item')->first();

        if ($existingSuggestion instanceof PhotoItemSuggestion) {
            return response()->json($existingSuggestion);
        }

        $prediction = $action->run($photo);

        if (! $prediction instanceof PhotoItemPrediction) {
            return response()->json([
                'error' => 'Failed to connect to LitterBot service',
            ], 422);
        }

        $item = $getItemFromPredictionAction->run($prediction);

        if (! $item instanceof Item || $photo->items()->where('item_id', $item->id)->exists()) {
            return response()->json();
        }

        $suggestion = $photo->photoItemSuggestions()->create([
            'item_id' => $item->id,
            'score' => $prediction->score,
        ]);

        return response()->json($suggestion->load('item'));
    }
}
