<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\PhotoItemTag;
use App\Models\Tag;
use App\Models\User;

test('a user can remove an item or tag from many photos at once', function (): void {
    $user = User::factory()->create();

    $photoA = Photo::factory()->create(['user_id' => $user->id]);
    $itemA = Item::factory()->create();
    $photoItemA = PhotoItem::factory()->for($itemA)->for($photoA)->create();
    $tagA = Tag::factory()->create();
    PhotoItemTag::factory()->for($photoItemA)->for($tagA)->create();

    $photoB = Photo::factory()->create(['user_id' => $user->id]);
    $itemB = Item::factory()->create();
    $photoItemB = PhotoItem::factory()->for($itemB)->for($photoB)->create();
    $tagB = Tag::factory()->create();
    PhotoItemTag::factory()->for($photoItemB)->for($tagB)->create();

    $this->assertDatabaseCount('photo_items', 2);
    $this->assertDatabaseCount('photo_item_tag', 2);

    $response = $this->actingAs($user)->deleteJson('/photos/items', [
        'photo_ids' => [$photoA->id, $photoB->id],
        'item_ids' => [$itemA->id],
        'tag_ids' => [$tagB->id],
    ]);

    $response->assertOk();
    $this->assertDatabaseCount('photo_items', 1);
    $this->assertDatabaseHas('photo_items', ['id' => $photoItemB->id]);
    $this->assertDatabaseEmpty('photo_item_tag');
});

test('a user can not remove an item from photos of another user', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();
    $existingItem = Item::factory()->create();
    $photo->items()->attach($existingItem);

    $this->assertDatabaseCount('photo_items', 1);

    $response = $this->actingAs($user)->deleteJson('/photos/items', [
        'photo_ids' => [$photo->id],
        'item_ids' => [$existingItem->id],
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('photo_ids');
    $this->assertDatabaseCount('photo_items', 1);
});
