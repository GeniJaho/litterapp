<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\Tag;
use App\Models\User;

test('a user can add tags to an item of a photo', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photo)->create();
    $tagA = Tag::factory()->create();
    $tagB = Tag::factory()->create();

    $response = $this->actingAs($user)->postJson("/photo-items/{$photoItem->id}/tags", [
        'tag_ids' => [$tagA->id, $tagB->id],
    ]);

    $response->assertOk();
    $this->assertDatabaseCount('photo_item_tag', 2);
    $this->assertDatabaseHas('photo_item_tag', [
        'photo_item_id' => $photoItem->id,
        'tag_id' => $tagA->id,
    ]);
    $this->assertDatabaseHas('photo_item_tag', [
        'photo_item_id' => $photoItem->id,
        'tag_id' => $tagB->id,
    ]);
});

test('the tags must exist', function (): void {
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

test('the tags must not be deprecated', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photo)->create();
    $tag = Tag::factory()->create(['deleted_at' => now()]);

    $response = $this->actingAs($user)->postJson("/photo-items/{$photoItem->id}/tags", [
        'tag_ids' => [$tag->id],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('tag_ids.0');
    $this->assertDatabaseCount('photo_item_tag', 0);
});

test('a user can not add a tag to an item of a photo of another user', function (): void {
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
