<?php

namespace App\Actions\Photos;

use App\Actions\AI\GetLitterBotUrlAction;
use App\DTO\PhotoSuggestionResult;
use App\Models\Photo;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SuggestPhotoTagsAction implements SuggestsPhotoTags
{
    public function __construct(
        protected GetLitterBotUrlAction $getLitterBotUrl,
    ) {}

    /**
     * @throws ConnectionException
     */
    public function run(Photo $photo): ?PhotoSuggestionResult
    {
        $response = Http::timeout(15)->post("{$this->getLitterBotUrl->run()}/predict", [
            'photo_url' => $photo->full_path,
        ]);

        if ($response->failed()) {
            Log::error('Failed to get photo suggestions', [
                'photo_id' => $photo->id,
                'response' => $response->body(),
            ]);

            return null;
        }

        Log::info('Received photo suggestions from LitterBot', [
            'photo_id' => $photo->id,
            'response' => $response->json(),
        ]);

        /** @var array<int, array{id: int, name: string, confidence: float, count: int}> $items */
        $items = $response->json('items', []);
        /** @var array<int, array{id: int, name: string, confidence: float, count: int}> $brands */
        $brands = $response->json('brands', []);
        /** @var array<int, array{id: int, name: string, confidence: float, count: int}> $content */
        $content = $response->json('content', []);

        return new PhotoSuggestionResult(
            items: $items,
            brands: $brands,
            content: $content,
        );
    }
}
