<?php

use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

test('guests are redirected to login from the dashboard', function (): void {
    $response = $this->get('/dashboard');

    $response->assertRedirect(route('login'));
});

test('an authenticated user can view the dashboard', function (): void {
    $this->actingAs(User::factory()->create());

    $response = $this->get('/dashboard');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Dashboard')
        ->etc()
    );
});

test('the home page stays public', function (): void {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Home')
        ->etc()
    );
});
