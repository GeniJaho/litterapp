<?php

use App\Models\Announcement;
use Carbon\Carbon;

test('published scope excludes drafts', function (): void {
    Announcement::factory()->create(['title' => 'Live']);
    Announcement::factory()->draft()->create(['title' => 'Draft']);

    $titles = Announcement::query()->published()->pluck('title')->all();

    expect($titles)->toBe(['Live']);
});

test('published scope excludes future-dated announcements', function (): void {
    Announcement::factory()->create(['title' => 'Live']);
    Announcement::factory()->scheduled()->create(['title' => 'Scheduled']);

    $titles = Announcement::query()->published()->pluck('title')->all();

    expect($titles)->toBe(['Live']);
});

test('published_at is cast to a datetime', function (): void {
    $announcement = Announcement::factory()->create();

    expect($announcement->published_at)->toBeInstanceOf(Carbon::class);
});
