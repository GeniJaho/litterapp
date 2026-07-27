<?php

use App\Actions\AI\IsLitterBotEnabledAction;
use App\Models\AppSetting;

it('is enabled when the setting does not exist', function (): void {
    expect(app(IsLitterBotEnabledAction::class)->run())->toBeTrue();
});

it('is disabled when the setting holds a falsy value', function (string $value): void {
    AppSetting::query()->create(['key' => 'litterbot_enabled', 'value' => $value]);

    expect(app(IsLitterBotEnabledAction::class)->run())->toBeFalse();
})->with(['0', 'false', 'off', 'no']);

it('is enabled when the setting holds a truthy value', function (string $value): void {
    AppSetting::query()->create(['key' => 'litterbot_enabled', 'value' => $value]);

    expect(app(IsLitterBotEnabledAction::class)->run())->toBeTrue();
})->with(['1', 'true', 'on', 'yes']);

it('stays enabled when the setting holds a value it cannot parse', function (): void {
    AppSetting::query()->create(['key' => 'litterbot_enabled', 'value' => 'banana']);

    expect(app(IsLitterBotEnabledAction::class)->run())->toBeTrue();
});

it('caches the setting lookup', function (): void {
    AppSetting::query()->create(['key' => 'litterbot_enabled', 'value' => '0']);

    expect(app(IsLitterBotEnabledAction::class)->run())->toBeFalse();

    AppSetting::query()->where('key', 'litterbot_enabled')->delete();

    expect(app(IsLitterBotEnabledAction::class)->run())->toBeFalse();

    cache()->forget(IsLitterBotEnabledAction::CACHE_KEY);

    expect(app(IsLitterBotEnabledAction::class)->run())->toBeTrue();
});
