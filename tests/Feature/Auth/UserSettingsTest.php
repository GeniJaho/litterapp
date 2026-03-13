<?php

use App\Models\User;
use Illuminate\Support\Carbon;

test('a user can update their settings', function (): void {
    Carbon::setTestNow('2026-03-05 08:00:00');

    $user = User::factory()->create([
        'settings' => [
            'picked_up_by_default' => false,
            'consent_to_training_at' => null,
        ],
    ]);

    $response = $this->actingAs($user)->post('/settings', [
        'picked_up_by_default' => true,
        'consent_to_training_at' => '2026-01-01T00:00:00+00:00',
    ]);

    $response->assertOk();

    expect($user->fresh()->settings->picked_up_by_default)->toBeTrue();
    expect($user->fresh()->settings->consent_to_training_at)->toBe('2026-03-05T08:00:00+00:00');
});

test('the request is validated', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/settings', [
        'picked_up_by_default' => 'not-a-boolean',
    ]);

    $response->assertSessionHasErrors('picked_up_by_default');
});

test('enabling training consent stores the consent timestamp', function (): void {
    Carbon::setTestNow('2026-03-05 11:22:33');

    $user = User::factory()->create([
        'settings' => [
            'consent_to_training_at' => null,
        ],
    ]);

    $response = $this->actingAs($user)->post('/settings', [
        'picked_up_by_default' => true,
        'consent_to_training_at' => '2026-01-01T00:00:00+00:00',
    ]);

    $response->assertOk();

    expect($user->fresh()->settings->consent_to_training_at)->toBe('2026-03-05T11:22:33+00:00');
});

test('disabling training consent clears the consent timestamp', function (): void {
    $user = User::factory()->create([
        'settings' => [
            'consent_to_training_at' => '2026-03-05T11:22:33+00:00',
        ],
    ]);

    $response = $this->actingAs($user)->post('/settings', [
        'picked_up_by_default' => true,
        'consent_to_training_at' => null,
    ]);

    $response->assertOk();

    expect($user->fresh()->settings->consent_to_training_at)->toBeNull();
});
