<?php

namespace App\Actions\Photos;

use App\DTO\PhotoItemPrediction;
use App\Models\AppSetting;
use App\Models\Photo;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClassifyPhotoAction implements ClassifiesPhoto
{
    public const LITTERBOT_URL_CACHE_KEY = 'classify_photo_action_litterbot_url';

    public function __construct(
        #[Config('services.litterbot.url')] protected string $litterBotUrl,
    ) {}

    /**
     * @throws ConnectionException
     */
    public function run(Photo $photo): ?PhotoItemPrediction
    {
        $response = Http::timeout(5)->post("{$this->getLitterBotUrl()}/predict", [
            'image_path' => $photo->full_path,
        ]);

        if ($response->failed()) {
            Log::error('Failed to get image prediction', [
                'photo_id' => $photo->id,
                'response' => $response->body(),
            ]);

            return null;
        }

        /** @var string $className */
        $className = $response->json('class_name');
        /** @var float $score */
        $score = $response->json('score');

        return new PhotoItemPrediction($className, $score);
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
