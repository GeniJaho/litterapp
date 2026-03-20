<?php

use App\Models\Photo;
use App\Models\User;

test('a user can get photo IDs in a range', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $photoC = Photo::factory()->for($user)->create();
    $photoD = Photo::factory()->for($user)->create();

    $response = $this->get("/photos/range-ids?start_id={$photoA->id}&end_id={$photoD->id}");

    $response->assertSuccessful();
    $response->assertJson([
        'photo_ids' => [$photoD->id, $photoC->id, $photoB->id, $photoA->id],
    ]);
});

test('a user can get photo IDs in a range regardless of start/end order', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $photoC = Photo::factory()->for($user)->create();

    $response = $this->get("/photos/range-ids?start_id={$photoC->id}&end_id={$photoA->id}");

    $response->assertSuccessful();
    $response->assertJson([
        'photo_ids' => [$photoC->id, $photoB->id, $photoA->id],
    ]);
});

test('a user cannot get photo IDs in a range that includes another users photos', function (): void {
    $this->actingAs($user = User::factory()->create());
    $otherUser = User::factory()->create();

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($otherUser)->create();
    $photoC = Photo::factory()->for($user)->create();

    $response = $this->get("/photos/range-ids?start_id={$photoA->id}&end_id={$photoC->id}");

    $response->assertSuccessful();
    $response->assertJson([
        'photo_ids' => [$photoC->id, $photoA->id],
    ]);
});

test('a user can get photo IDs in a range respecting sort settings', function (): void {
    $this->actingAs($user = User::factory()->create([
        'settings' => new \App\DTO\UserSettings(sort_column: 'taken_at_local', sort_direction: 'asc'),
    ]));

    $photoA = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinutes(3)]);
    $photoB = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinutes(1)]);
    $photoC = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinutes(2)]);

    $response = $this->get("/photos/range-ids?start_id={$photoB->id}&end_id={$photoC->id}");

    $response->assertSuccessful();
    $response->assertJson([
        'photo_ids' => [$photoB->id, $photoC->id],
    ]);
});

test('a user can get photo IDs in a range with filters applied', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $photoC = Photo::factory()->for($user)->create();

    $user->settings->photo_filters = new \App\DTO\PhotoFilters(item_ids: []);
    $user->save();

    $response = $this->get("/photos/range-ids?start_id={$photoA->id}&end_id={$photoC->id}");

    $response->assertSuccessful();
    $response->assertJson([
        'photo_ids' => [$photoC->id, $photoB->id, $photoA->id],
    ]);
});
