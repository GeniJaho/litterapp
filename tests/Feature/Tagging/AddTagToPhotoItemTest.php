<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\Tag;
use App\Models\User;

test('a user can add a tag to an item of a photo', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photo)->create();
    $tag = Tag::factory()->create();

    $response = $this->actingAs($user)->postJson("/photo-items/{$photoItem->id}/tags", [
        'tag_ids' => [$tag->id],
    ]);

    $response->assertOk();
    $this->assertDatabaseCount('photo_item_tag', 1);
    $this->assertDatabaseHas('photo_item_tag', [
        'photo_item_id' => $photoItem->id,
        'tag_id' => $tag->id,
    ]);
});

test('the request is validated', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photo)->create();

    $response = $this->actingAs($user)->postJson("/photo-items/{$photoItem->id}/tags", [
        'tag_ids' => ['12345'],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('tag_ids.0');
    $this->assertDatabaseCount('photo_item_tag', 0);
});

test('a user can not add a tag to an item of a photo of another user', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();
    $item = Item::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photo)->create();
    $tag = Tag::factory()->create();

    $response = $this->actingAs($user)->postJson("/photo-items/{$photoItem->id}/tags", [
        'tag_ids' => [$tag->id],
    ]);

    $response->assertNotFound();
    $this->assertDatabaseCount('photo_item_tag', 0);
});
