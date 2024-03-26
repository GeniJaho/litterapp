<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\PhotoItemTag;
use App\Models\Tag;
use App\Models\User;

test('a user can duplicate an item and its tags on a photo', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photo)->create();
    $tag = Tag::factory()->create();
    PhotoItemTag::factory()->for($photoItem)->for($tag)->create();

    $response = $this->actingAs($user)->postJson("/photo-items/{$photoItem->id}/copy");

    $response->assertOk();
    $this->assertDatabaseCount('photo_items', 2);
    $this->assertDatabaseCount('photo_item_tag', 2);

    $latestPhotoItem = PhotoItem::latest('id')->first();
    $this->assertEquals($photoItem->item_id, $latestPhotoItem->item_id);
    $this->assertEquals($photoItem->photo_id, $latestPhotoItem->photo_id);

    $latestPhotoItemTag = PhotoItemTag::latest('id')->first();
    $this->assertEquals($latestPhotoItem->id, $latestPhotoItemTag->photo_item_id);
    $this->assertEquals($tag->id, $latestPhotoItemTag->tag_id);
});

test('a user can not duplicate an item and its tags on a photo of another user', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();
    $item = Item::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photo)->create();
    $tag = Tag::factory()->create();
    PhotoItemTag::factory()->for($photoItem)->for($tag)->create();

    $response = $this->actingAs($user)->postJson("/photo-items/{$photoItem->id}/copy");

    $response->assertNotFound();
    $this->assertDatabaseCount('photo_items', 1);
    $this->assertDatabaseCount('photo_item_tag', 1);
});
