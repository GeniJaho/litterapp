<?php

use App\DTO\PhotoFilters;
use App\DTO\UserSettings;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

test('all photo ids are passed in sorted order', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $photoC = Photo::factory()->for($user)->create();

    $response = $this->get('/my-photos');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->where('allPhotoIds', [$photoC->id, $photoB->id, $photoA->id])
        ->etc()
    );
});

test('all photo ids exclude other users photos', function (): void {
    $this->actingAs($user = User::factory()->create());
    $otherUser = User::factory()->create();

    $photoA = Photo::factory()->for($user)->create();
    Photo::factory()->for($otherUser)->create();
    $photoC = Photo::factory()->for($user)->create();

    $response = $this->get('/my-photos');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->where('allPhotoIds', [$photoC->id, $photoA->id])
        ->etc()
    );
});

test('all photo ids respect non-id sort settings', function (): void {
    $this->actingAs($user = User::factory()->create([
        'settings' => new UserSettings(sort_column: 'taken_at_local', sort_direction: 'asc'),
    ]));

    // IDs are sequential (A < B < C < D), but sorted by taken_at_local: B, C, A, D
    $photoA = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinutes(3)]);
    $photoB = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinutes(1)]);
    $photoC = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinutes(2)]);
    $photoD = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinutes(4)]);

    $response = $this->get('/my-photos');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->where('allPhotoIds', [$photoB->id, $photoC->id, $photoA->id, $photoD->id])
        ->etc()
    );
});

test('all photo ids respect filters', function (): void {
    $this->actingAs($user = User::factory()->create());

    Photo::factory()->for($user)->create();
    Photo::factory()->for($user)->create();
    Photo::factory()->for($user)->create();

    $user->settings->photo_filters = new PhotoFilters(item_ids: []);
    $user->save();

    $response = $this->get('/my-photos');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('allPhotoIds', 3)
        ->etc()
    );
});
