<?php

use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;

test('a user can tag a photo', function () {
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

test('a user can not tag a photo with the same tag more than once', function () {

})->todo();
