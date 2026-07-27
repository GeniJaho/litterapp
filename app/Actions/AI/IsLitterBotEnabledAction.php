<?php

declare(strict_types=1);

namespace App\Actions\AI;

use App\Models\AppSetting;

class IsLitterBotEnabledAction
{
    public const CACHE_KEY = 'litterbot_enabled_setting';

    /**
     * LitterBot is enabled unless the `litterbot_enabled` app setting explicitly says otherwise.
     */
    public function run(): bool
    {
        $valueFromSettings = cache()->remember(
            self::CACHE_KEY,
            now()->addHour(),
            fn () => AppSetting::query()->where('key', 'litterbot_enabled')->value('value')
        );

        if (is_string($valueFromSettings)) {
            $parsed = filter_var($valueFromSettings, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if (is_bool($parsed)) {
                return $parsed;
            }
        }

        return true;
    }
}
