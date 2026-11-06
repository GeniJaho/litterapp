<?php

use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

test('a user can not see the admin notes about them', function (): void {
    $this->actingAs($user = User::factory()->create(['admin_notes' => 'Messaged on Facebook as litterhero']));

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('auth.user.id', $user->id)
        ->etc()
    );
    $response->assertDontSee('Messaged on Facebook as litterhero');
});
