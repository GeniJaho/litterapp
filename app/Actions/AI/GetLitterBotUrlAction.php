<?php

namespace App\Actions\AI;

use App\Models\AppSetting;
use Illuminate\Container\Attributes\Config;

class GetLitterBotUrlAction
{
    public const CACHE_KEY = 'suggest_photo_tags_action_litterbot_url';

    public function __construct(
        #[Config('services.litterbot.url')] protected string $defaultUrl,
    ) {}

    public function run(): string
    {
        $valueFromSettings = cache()->remember(
            self::CACHE_KEY,
            now()->addSeconds(10),
            fn () => AppSetting::query()->where('key', 'litterbot_url')->value('value')
        );

        if (is_string($valueFromSettings) && $valueFromSettings !== '') {
            return $valueFromSettings;
        }

        return $this->defaultUrl;
    }
}
