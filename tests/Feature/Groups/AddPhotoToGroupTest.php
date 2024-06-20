<?php

use App\Models\Group;
use App\Models\Photo;
use App\Models\User;

test('a user can add photos to a group', function (): void {
    $user = User::factory()->create();
    $group = Group::factory()->create(['user_id' => $user->id]);
    $existingPhoto = Photo::factory()->create(['user_id' => $user->id]);
    $group->photos()->attach($existingPhoto);
    [$photoA, $photoB] = Photo::factory(2)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson("/groups/{$group->id}/photos", [
        'photo_ids' => [$photoA->id, $photoB->id],
    ]);

    $response->assertOk();
    $this->assertDatabaseCount('group_photo', 3);
    $this->assertDatabaseHas('group_photo', [
        'group_id' => $group->id,
        'photo_id' => $photoA->id,
    ]);
    $this->assertDatabaseHas('group_photo', [
        'group_id' => $group->id,
        'photo_id' => $photoB->id,
    ]);
});

test('a user can not add a photo more than once to a group', function (): void {
    $user = User::factory()->create();
    $group = Group::factory()->create(['user_id' => $user->id]);
    [$photoA, $photoB] = Photo::factory(2)->create(['user_id' => $user->id]);
    $group->photos()->attach($photoA);
    $group->photos()->attach($photoB);

    $response = $this->actingAs($user)->postJson("/groups/{$group->id}/photos", [
        'photo_ids' => [$photoA->id],
    ]);

    $response->assertOk();
    $this->assertDatabaseCount('group_photo', 2);
});

test('the request is validated', function (): void {
    $user = User::factory()->create();
    $group = Group::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson("/groups/{$group->id}/photos", [
        'photo_ids' => ['1'],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('photo_ids.0');
});

test('a user can not add a photo to another users group', function (): void {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson("/groups/{$group->id}/photos", [
        'photo_ids' => [$photo->id],
    ]);

    $response->assertNotFound();
});

test('a user can not add a photo they do not own to a group', function (): void {
    $user = User::factory()->create();
    $group = Group::factory()->create(['user_id' => $user->id]);
    $photo = Photo::factory()->create();

    $response = $this->actingAs($user)->postJson("/groups/{$group->id}/photos", [
        'photo_ids' => [$photo->id],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('photo_ids');
});
