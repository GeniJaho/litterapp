<?php

use App\Models\Announcement;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

test('anyone can view the announcements archive', function (): void {
    $response = $this->get('/announcements');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Announcements/Index')
        ->has('announcements.data')
        ->has('announcements.links')
        ->etc()
    );
});

test('announcements archive only lists published announcements ordered by date', function (): void {
    $newest = Announcement::factory()->create(['published_at' => now()->subDay()]);
    $older = Announcement::factory()->create(['published_at' => now()->subDays(7)]);
    Announcement::factory()->draft()->create();
    Announcement::factory()->scheduled()->create();

    $response = $this->get('/announcements');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Announcements/Index')
        ->has('announcements.data', 2)
        ->where('announcements.data.0.id', $newest->id)
        ->where('announcements.data.1.id', $older->id)
        ->etc()
    );
});
