<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\PhotoItemTag;
use App\Models\Tag;
use App\Models\User;

test('a user can remove a tag from an item of a photo', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photo)->create();
    $tag = Tag::factory()->create();
    PhotoItemTag::factory()->for($photoItem)->for($tag)->create();

    $response = $this->actingAs($user)->deleteJson("/photo-items/{$photoItem->id}/tags/{$tag->id}");

    $response->assertOk();
    $this->assertDatabaseEmpty('photo_item_tag');
});
