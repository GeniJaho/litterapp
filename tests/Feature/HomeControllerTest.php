<?php

use App\Models\Announcement;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

test('anyone can view the home page', function (): void {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Home')
        ->etc()
    );
});

test('home page exposes the latest three published announcements', function (): void {
    $older = Announcement::factory()->create(['published_at' => now()->subDays(5)]);
    $middle = Announcement::factory()->create(['published_at' => now()->subDays(3)]);
    $newest = Announcement::factory()->create(['published_at' => now()->subDay()]);
    $oldest = Announcement::factory()->create(['published_at' => now()->subDays(10)]);
    Announcement::factory()->draft()->create();
    Announcement::factory()->scheduled()->create();

    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Home')
        ->has('announcements', 3)
        ->where('announcements.0.id', $newest->id)
        ->where('announcements.1.id', $middle->id)
        ->where('announcements.2.id', $older->id)
        ->etc()
    );
});
