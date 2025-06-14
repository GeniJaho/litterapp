<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Photo;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class LitterBotController extends Controller
{
    public function suggest(Photo $photo): JsonResponse
    {
        $response = Http::timeout(10)->post('http://127.0.0.1:8001/predict', [
            'image_path' => $photo->full_path,
        ]);

        if ($response->failed()) {
            return response()->json([
                'error' => 'Failed to connect to LitterBot service',
            ], 422);
        }

        $score = $response->json('score');
        $itemClass = $response->json('class_name');

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

        $item = Item::query()->where('name', $itemClassNames[$itemClass])->first();

        if (! $item instanceof Item || $photo->items()->where('item_id', $item->id)->exists()) {
            return response()->json([
                'item' => null,
                'score' => null,
            ]);
        }

        return response()->json([
            'item' => $item,
            'score' => $score,
        ]);
    }
}
