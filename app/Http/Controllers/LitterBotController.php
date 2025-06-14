<?php

namespace App\Http\Controllers;

use App\Actions\Photos\ClassifyPhotoAction;
use App\DTO\PhotoItemPrediction;
use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItemSuggestion;
use Illuminate\Http\JsonResponse;

class LitterBotController extends Controller
{
    public function suggest(Photo $photo, ClassifyPhotoAction $action): JsonResponse
    {
        $existingSuggestion = $photo->photoItemSuggestions()->with('item')->first();

        if ($existingSuggestion instanceof PhotoItemSuggestion) {
            return response()->json([
                'item' => $existingSuggestion->item,
                'score' => $existingSuggestion->score,
            ]);
        }

        $prediction = $action->run($photo);

        if (! $prediction instanceof PhotoItemPrediction) {
            return response()->json([
                'error' => 'Failed to connect to LitterBot service',
            ], 422);
        }

        $item = $this->findItem($prediction);

        if (! $item instanceof Item || $photo->items()->where('item_id', $item->id)->exists()) {
            return response()->json([
                'item' => null,
                'score' => null,
            ]);
        }

        $photo->photoItemSuggestions()->create([
            'item_id' => $item->id,
            'score' => $prediction->score,
        ]);

        return response()->json([
            'item' => $item,
            'score' => $prediction->score,
        ]);
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

        return Item::query()->where('name', $itemClassNames[$prediction->class_name])->first();
    }
}
