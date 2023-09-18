<?php

use App\Models\Item;
use App\Models\PhotoItem;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;

test('a user can add a tag to an item of a photo', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $itemPhoto = PhotoItem::factory()->for($item)->for($photo)->create();
    $tag = Tag::factory()->create();

    $response = $this->actingAs($user)->postJson("/photos/{$photo->id}/items/{$item->id}/tags", [
        'tag_id' => $tag->id,
    ]);

    $response->assertOk();
    $this->assertDatabaseCount('photo_item_tag', 1);
    $this->assertDatabaseHas('photo_item_tag', [
        'photo_id' => $photo->id,
        'item_id' => $item->id,
        'tag_id' => $tag->id,
    ]);
});
