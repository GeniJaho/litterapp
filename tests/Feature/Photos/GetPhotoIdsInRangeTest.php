<?php

use App\DTO\PhotoFilters;
use App\DTO\UserSettings;
use App\Models\Photo;
use App\Models\User;

test('all photo ids are returned in sorted order', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $photoC = Photo::factory()->for($user)->create();

    $this->getJson('/my-photos/ids')
        ->assertOk()
        ->assertExactJson([$photoC->id, $photoB->id, $photoA->id]);
});

test('all photo ids exclude other users photos', function (): void {
    $this->actingAs($user = User::factory()->create());
    $otherUser = User::factory()->create();

    $photoA = Photo::factory()->for($user)->create();
    Photo::factory()->for($otherUser)->create();
    $photoC = Photo::factory()->for($user)->create();

    $this->getJson('/my-photos/ids')
        ->assertOk()
        ->assertExactJson([$photoC->id, $photoA->id]);
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

    $this->getJson('/my-photos/ids')
        ->assertOk()
        ->assertExactJson([$photoB->id, $photoC->id, $photoA->id, $photoD->id]);
});

test('all photo ids respect filters', function (): void {
    $this->actingAs($user = User::factory()->create());

    Photo::factory()->for($user)->count(3)->create();

    $user->settings->photo_filters = new PhotoFilters(item_ids: []);
    $user->save();

    $this->getJson('/my-photos/ids')
        ->assertOk()
        ->assertJsonCount(3);
});

test('guest cannot fetch photo ids', function (): void {
    $this->getJson('/my-photos/ids')->assertUnauthorized();
});
