<?php

use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\PhotoItemTag;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

test('guests are redirected to login from the dashboard', function (): void {
    $response = $this->get('/dashboard');

    $response->assertRedirect(route('login'));
});

test('an authenticated user can view the dashboard before the stats arrive', function (): void {
    $this->actingAs(User::factory()->create());

    $response = $this->get('/dashboard');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Dashboard')
        ->where('period', 'all')
        ->has('availablePeriods', 3)
        ->missing('stats')
        ->etc()
    );
});

test('the dashboard reports all-time stats scoped to the authenticated user', function (): void {
    $this->travelTo(Carbon::parse('2026-06-22 12:00:00'));

    $user = User::factory()->create();
    $other = User::factory()->create();

    $photoA = Photo::factory()->for($user)->create(['taken_at_local' => now()]);
    $photoB = Photo::factory()->for($user)->create(['taken_at_local' => now()]);

    // An untagged photo (still needs tags).
    Photo::factory()->for($user)->create(['taken_at_local' => now()]);

    // Taken two years ago: counts all-time but not within this year.
    $photoOld = Photo::factory()->for($user)->create(['taken_at_local' => now()->subYears(2)]);

    // Another user's data must be excluded entirely.
    $otherPhoto = Photo::factory()->for($other)->create(['taken_at_local' => now()]);

    $itemA = PhotoItem::factory()->for($photoA)->create([
        'quantity' => 3,
        'picked_up' => true,
        'recycled' => true,
        'deposit' => false,
    ]);
    PhotoItem::factory()->for($photoB)->create([
        'quantity' => 1,
        'picked_up' => true,
        'recycled' => false,
        'deposit' => true,
    ]);
    PhotoItem::factory()->for($photoOld)->create([
        'quantity' => 10,
        'picked_up' => true,
        'recycled' => false,
        'deposit' => false,
    ]);
    $otherItem = PhotoItem::factory()->for($otherPhoto)->create(['quantity' => 5]);

    foreach (Tag::factory()->count(2)->create() as $tag) {
        PhotoItemTag::factory()->create([
            'photo_item_id' => $itemA->id,
            'tag_id' => $tag->id,
        ]);
    }

    PhotoItemTag::factory()->create([
        'photo_item_id' => $otherItem->id,
        'tag_id' => Tag::factory()->create()->id,
    ]);

    $this->actingAs($user);

    $this->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Dashboard')
            ->where('period', 'all')
            ->loadDeferredProps(fn (AssertableInertia $stats): AssertableJson => $stats
                ->where('stats.totals.photos', 4)
                ->where('stats.totals.items', 14)
                ->where('stats.totals.tags', 2)
                ->where('stats.needs_tagging', 1)
                ->where('stats.charts.activity.labels', ['2024', '2025', '2026'])
                ->where('stats.charts.activity.photos', [1, 0, 3])
                ->where('stats.charts.activity.items', [10, 0, 4])
                ->where('stats.charts.top_items.quantities', [10, 3, 1])
                ->etc()
            )
        );
});

test('the dashboard weights the disposal split by quantity, not by row', function (): void {
    $this->travelTo(Carbon::parse('2026-06-22 12:00:00'));

    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create(['taken_at_local' => now()]);

    PhotoItem::factory()->for($photo)->create([
        'quantity' => 3,
        'picked_up' => true,
        'recycled' => true,
        'deposit' => false,
    ]);
    PhotoItem::factory()->for($photo)->create([
        'quantity' => 11,
        'picked_up' => true,
        'recycled' => false,
        'deposit' => true,
    ]);

    $this->actingAs($user);

    $this->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->loadDeferredProps(fn (AssertableInertia $stats): AssertableJson => $stats
                ->where('stats.totals.items', 14)
                ->where('stats.charts.disposal.picked_up', 14)
                ->where('stats.charts.disposal.recycled', 3)
                ->where('stats.charts.disposal.deposit', 11)
                ->etc()
            )
        );
});

test('the dashboard filters stats by the selected period', function (): void {
    $this->travelTo(Carbon::parse('2026-06-22 12:00:00'));

    $user = User::factory()->create();

    $photoThisYear = Photo::factory()->for($user)->create(['taken_at_local' => now()]);
    $photoOld = Photo::factory()->for($user)->create(['taken_at_local' => now()->subYears(2)]);

    PhotoItem::factory()->for($photoThisYear)->create([
        'quantity' => 4,
        'picked_up' => true,
        'recycled' => true,
        'deposit' => false,
    ]);
    PhotoItem::factory()->for($photoOld)->create([
        'quantity' => 10,
        'picked_up' => true,
        'recycled' => true,
        'deposit' => true,
    ]);

    $this->actingAs($user);

    $this->get('/dashboard?period=this_year')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Dashboard')
            ->where('period', 'this_year')
            ->loadDeferredProps(fn (AssertableInertia $stats): AssertableJson => $stats
                ->where('stats.totals.photos', 1)
                ->where('stats.totals.items', 4)
                ->where('stats.charts.disposal.picked_up', 4)
                ->where('stats.charts.disposal.recycled', 4)
                ->where('stats.charts.disposal.deposit', 0)
                ->etc()
            )
        );
});

test('an unknown period falls back to all time', function (): void {
    $this->actingAs(User::factory()->create());

    $this->get('/dashboard?period=nope')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
            ->where('period', 'all')
            ->etc()
        );
});

test('a stray old photo date does not stretch the year axis', function (): void {
    $this->travelTo(Carbon::parse('2026-06-22 12:00:00'));

    $user = User::factory()->create();

    // A broken EXIF date should not add decades of empty year buckets.
    Photo::factory()->for($user)->create(['taken_at_local' => Carbon::parse('1970-10-19 10:56:13')]);
    Photo::factory()->for($user)->create(['taken_at_local' => now()]);

    $this->actingAs($user);

    $this->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->loadDeferredProps(fn (AssertableInertia $stats): AssertableJson => $stats
                ->where('stats.totals.photos', 2)
                ->where('stats.charts.activity.labels', ['2026'])
                ->where('stats.charts.activity.photos', [1])
                ->etc()
            )
        );
});

test('the year axis still shows photos that all predate the capped span', function (): void {
    $this->travelTo(Carbon::parse('2026-06-22 12:00:00'));

    $user = User::factory()->create();

    Photo::factory()->for($user)->create(['taken_at_local' => Carbon::parse('2010-04-01 09:00:00')]);

    $this->actingAs($user);

    $this->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->loadDeferredProps(fn (AssertableInertia $stats): AssertableJson => $stats
                ->where('stats.charts.activity.labels', ['2010'])
                ->where('stats.charts.activity.photos', [1])
                ->etc()
            )
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
