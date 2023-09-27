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
        'tag_id' => $tag->id,
    ]);

    $response->assertOk();
    $this->assertDatabaseCount('photo_item_tag', 1);
    $this->assertDatabaseHas('photo_item_tag', [
        'photo_item_id' => $photoItem->id,
        'tag_id' => $tag->id,
    ]);
});
