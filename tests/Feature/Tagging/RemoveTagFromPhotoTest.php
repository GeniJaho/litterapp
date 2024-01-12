<?php

use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;

test('a user can remove a tag from a photo', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $existingTag = Tag::factory()->create();
    $photo->tags()->attach($existingTag);

    $this->assertDatabaseCount('photo_tag', 1);

    $response = $this->actingAs($user)->deleteJson("/photos/{$photo->id}/tags/{$existingTag->id}");

    $response->assertOk();
    $this->assertDatabaseEmpty('photo_tag');
});

test('a user can not remove a tag from a photo of another user', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();
    $existingTag = Tag::factory()->create();
    $photo->tags()->attach($existingTag);

    $this->assertDatabaseCount('photo_tag', 1);

    $response = $this->actingAs($user)->deleteJson("/photos/{$photo->id}/tags/{$existingTag->id}");

    $response->assertNotFound();
    $this->assertDatabaseCount('photo_tag', 1);
});
