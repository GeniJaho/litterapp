<?php

namespace App\Actions\Photos;

use App\DTO\PhotoSuggestionResult;
use App\Models\AppSetting;
use App\Models\Photo;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SuggestPhotoTagsAction implements SuggestsPhotoTags
{
    public const LITTERBOT_URL_CACHE_KEY = 'suggest_photo_tags_action_litterbot_url';

    public function __construct(
        #[Config('services.litterbot.url')] protected string $litterBotUrl,
    ) {}

    /**
     * @throws ConnectionException
     */
    public function run(Photo $photo): ?PhotoSuggestionResult
    {
        $response = Http::timeout(15)->post("{$this->getLitterBotUrl()}/predict", [
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

        return new PhotoSuggestionResult(
            items: $response->json('items', []),
            brands: $response->json('brands', []),
            content: $response->json('content', []),
        );
    }

    private function getLitterBotUrl(): string
    {
        $valueFromSettings = cache()->remember(
            self::LITTERBOT_URL_CACHE_KEY,
            now()->addSeconds(10),
            fn () => AppSetting::query()->where('key', 'litterbot_url')->value('value')
        );

        if (is_string($valueFromSettings) && $valueFromSettings !== '') {
            return $valueFromSettings;
        }

        return $this->litterBotUrl;
    }
}
