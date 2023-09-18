<?php

use App\Models\Item;
use App\Models\PhotoItem;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;

test('a user can remove a tag from an item of a photo', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $itemPhoto = PhotoItem::factory()->for($item)->for($photo)->create();
    $tag = Tag::factory()->create();
    \App\Models\PhotoItemTag::factory()->for($item)->for($photo)->for($tag)->create();

    $response = $this->actingAs($user)->deleteJson("/photos/{$photo->id}/items/{$item->id}/tags/{$tag->id}");

    $response->assertOk();
    $this->assertDatabaseEmpty('photo_item_tag');
});
