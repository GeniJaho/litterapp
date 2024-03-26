<?php

use App\Models\User;

test('a user can update their settings', function (): void {
    $user = User::factory()->create([
        'settings' => [
            'picked_up_by_default' => false,
        ],
    ]);

    $response = $this->actingAs($user)->post('/settings', [
        'picked_up_by_default' => true,
    ]);

    $response->assertOk();

    $this->assertTrue($user->fresh()->settings->picked_up_by_default);
});

test('the request is validated', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/settings', [
        'picked_up_by_default' => 'not-a-boolean',
    ]);

    $response->assertSessionHasErrors('picked_up_by_default');
});
