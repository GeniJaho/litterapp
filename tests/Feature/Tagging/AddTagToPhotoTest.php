<?php

use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;

test('a user can add a tag to a photo', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $existingTag = Tag::factory()->create();
    $photo->tags()->attach($existingTag);
    $tag = Tag::factory()->create();

    $response = $this->actingAs($user)->postJson("/photos/{$photo->id}/tags", [
        'tag_id' => $tag->id,
    ]);

    $response->assertOk();
    $this->assertDatabaseCount('photo_tag', 2);
    $this->assertDatabaseHas('photo_tag', [
        'photo_id' => $photo->id,
        'tag_id' => $tag->id,
    ]);
});

test('the request is validated', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson("/photos/{$photo->id}/tags", [
        'tag_id' => '12345',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('tag_id');
    $this->assertDatabaseCount('photo_tag', 0);
});

test('a user can not add a tag to a photo of another user', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();
    $tag = Tag::factory()->create();

    $response = $this->actingAs($user)->postJson("/photos/{$photo->id}/tags", [
        'tag_id' => $tag->id,
    ]);

    $response->assertNotFound();
    $this->assertDatabaseCount('photo_tag', 0);
});
