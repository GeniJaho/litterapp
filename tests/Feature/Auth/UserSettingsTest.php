<?php

use App\Models\User;

test('a user can update their settings', function (): void {
    $user = User::factory()->create([
        'settings' => [
            'picked_up_by_default' => false,
            'consent_to_training' => false,
        ],
    ]);

    $response = $this->actingAs($user)->post('/settings', [
        'picked_up_by_default' => true,
        'consent_to_training' => true,
    ]);

    $response->assertOk();

    $this->assertTrue($user->fresh()->settings->picked_up_by_default);
    $this->assertTrue($user->fresh()->settings->consent_to_training);
});

test('the request is validated', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/settings', [
        'picked_up_by_default' => 'not-a-boolean',
    ]);

    $response->assertSessionHasErrors('picked_up_by_default');
});
