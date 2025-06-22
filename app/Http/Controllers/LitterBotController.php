<?php

namespace App\Http\Controllers;

use App\Actions\Photos\ClassifiesPhoto;
use App\DTO\PhotoItemPrediction;
use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItemSuggestion;
use App\Models\User;
use Illuminate\Container\Attributes\Config;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;

class LitterBotController extends Controller
{
    public function suggest(
        Photo $photo,
        ClassifiesPhoto $action,
        #[CurrentUser] User $user,
        #[Config('services.litterbot.enabled')] bool $litterBotEnabled
    ): JsonResponse {
        // This feature is only available to admins when it is enabled
        if (! $litterBotEnabled || ! $user->is_admin) {
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

        $item = $this->findItem($prediction);

        if (! $item instanceof Item || $photo->items()->where('item_id', $item->id)->exists()) {
            return response()->json();
        }

        $suggestion = $photo->photoItemSuggestions()->create([
            'item_id' => $item->id,
            'score' => $prediction->score,
        ]);

        return response()->json($suggestion->load('item'));
    }

    private function findItem(PhotoItemPrediction $prediction): ?Item
    {
        $itemClassNames = [
            'aluminium-foil' => 'Aluminium Foil',
            'balloon' => 'Balloon',
            'bottle' => 'Bottle',
            'can' => 'Can',
            'cap' => 'Cap (Bottle Cap/-Lid/-Top)',
            'cigarette-butt' => 'Cigarette Butt',
            'drink-pouch' => 'Drink Pouch',
            'straw' => 'Straw',
        ];

        if (! isset($itemClassNames[$prediction->class_name])) {
            return null;
        }

        return Item::query()->where('name', $itemClassNames[$prediction->class_name])->first();
    }
}
