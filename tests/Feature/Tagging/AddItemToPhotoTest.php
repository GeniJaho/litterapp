<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\User;

test('a user can add an item to a photo', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $existingItem = Item::factory()->create();
    $photo->items()->attach($existingItem);
    $item = Item::factory()->create();

    $response = $this->actingAs($user)->postJson("/photos/{$photo->id}/items", [
        'item_id' => $item->id,
    ]);

    $response->assertOk();
    $this->assertDatabaseCount('photo_items', 2);
    $this->assertDatabaseHas('photo_items', [
        'photo_id' => $photo->id,
        'item_id' => $item->id,
    ]);
});

test('a user can add an item more than once to a photo', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $existingItem = Item::factory()->create();
    $photo->items()->attach($existingItem);

    $response = $this->actingAs($user)->postJson("/photos/{$photo->id}/items", [
        'item_id' => $existingItem->id,
    ]);

    $response->assertOk();
    $this->assertDatabaseCount('photo_items', 2);
    $this->assertDatabaseHas('photo_items', [
        'photo_id' => $photo->id,
        'item_id' => $existingItem->id,
    ]);
});