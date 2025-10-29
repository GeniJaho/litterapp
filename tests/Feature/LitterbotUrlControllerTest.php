<?php

use App\Models\AppSetting;
use Illuminate\Testing\Fluent\AssertableJson;

it('updates the LitterBot URL when the key matches', function (): void {
    config(['services.litterbot.update_key' => 'super-secret-key']);

    $setting = AppSetting::query()->create([
        'key' => 'litterbot_url',
        'value' => 'https://old.example',
    ]);

    $response = $this->postJson('/api/litterbot/url', [
        'key' => 'super-secret-key',
        'url' => 'https://new.example',
    ]);

    $response->assertOk();
    $response->assertJson(fn (AssertableJson $json): AssertableJson => $json
        ->where('message', 'URL updated')
    );

    expect($setting->fresh()->value)->toBe('https://new.example');
});

it('returns 401 Unauthorized when the key does not match', function (): void {
    config(['services.litterbot.update_key' => 'expected-key']);

    $setting = AppSetting::query()->create([
        'key' => 'litterbot_url',
        'value' => 'https://keep.example',
    ]);

    $response = $this->postJson('/api/litterbot/url', [
        'key' => 'wrong-key',
        'url' => 'https://new.example',
    ]);

    $response->assertStatus(401);
    $response->assertJson(['message' => 'Unauthorized']);

    expect($setting->fresh()->value)->toBe('https://keep.example');
});

it('validates the request payload', function (?string $key, ?string $url): void {
    config(['services.litterbot.update_key' => 'k']);

    AppSetting::query()->create([
        'key' => 'litterbot_url',
        'value' => 'https://old.example',
    ]);

    $this->postJson('/api/litterbot/url', [
        'key' => $key,
        'url' => $url,
    ])->assertStatus(422);
})->with([
    ['k', null],
    [null, 'https://new.example'],
    ['k', 'not-a-url'],
    ['k', 'ftp://example.com'],
]);
