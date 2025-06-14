<?php

namespace App\Actions\Photos;

use App\DTO\PhotoItemPrediction;
use App\Models\Photo;
use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClassifyPhotoAction
{
    public function __construct(
        #[Config('services.litterbot.url')] protected string $litterBotUrl,
    ) {}

    public function run(Photo $photo): ?PhotoItemPrediction
    {
        $response = Http::timeout(5)->post("{$this->litterBotUrl}/predict", [
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
}
