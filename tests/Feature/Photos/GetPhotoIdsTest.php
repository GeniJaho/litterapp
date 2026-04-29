<?php

use App\DTO\UserSettings;
use App\Models\Photo;
use App\Models\User;

test('photo ids are returned up to the given limit', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $photoC = Photo::factory()->for($user)->create();

    $this->getJson('/my-photos/ids?limit=2')
        ->assertOk()
        ->assertExactJson([$photoC->id, $photoB->id]);
});

test('photo ids default to per page when no limit is given', function (): void {
    $this->actingAs($user = User::factory()->create([
        'settings' => new UserSettings(per_page: 25),
    ]));

    Photo::factory()->for($user)->count(3)->create();

    $this->getJson('/my-photos/ids')
        ->assertOk()
        ->assertJsonCount(3);
});

test('photo ids exclude other users photos', function (): void {
    $this->actingAs($user = User::factory()->create());
    $otherUser = User::factory()->create();

    $photoA = Photo::factory()->for($user)->create();
    Photo::factory()->for($otherUser)->create();
    $photoC = Photo::factory()->for($user)->create();

    $this->getJson('/my-photos/ids?limit=100')
        ->assertOk()
        ->assertExactJson([$photoC->id, $photoA->id]);
});

test('photo ids respect sort settings', function (): void {
    $this->actingAs($user = User::factory()->create([
        'settings' => new UserSettings(sort_column: 'taken_at_local', sort_direction: 'asc'),
    ]));

    $photoA = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinutes(3)]);
    $photoB = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinutes(1)]);
    $photoC = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinutes(2)]);

    $this->getJson('/my-photos/ids?limit=100')
        ->assertOk()
        ->assertExactJson([$photoB->id, $photoC->id, $photoA->id]);
});

test('guest cannot fetch photo ids', function (): void {
    $this->getJson('/my-photos/ids')->assertUnauthorized();
});
