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

test('if the user has enabled settings to pick up by default the item should be added as picked up', function () {
    $user = User::factory()->create(['settings' => ['picked_up_by_default' => true]]);
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $item = Item::factory()->create();

    $response = $this->actingAs($user)->postJson("/photos/{$photo->id}/items", [
        'item_id' => $item->id,
    ]);

    $response->assertOk();
    $this->assertDatabaseCount('photo_items', 1);
    $this->assertDatabaseHas('photo_items', [
        'photo_id' => $photo->id,
        'item_id' => $item->id,
        'picked_up' => true,
    ]);
});

test('if the user has enabled settings to recycle by default the item should be added as recycled', function () {
    $user = User::factory()->create(['settings' => ['recycled_by_default' => true]]);
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $item = Item::factory()->create();

    $response = $this->actingAs($user)->postJson("/photos/{$photo->id}/items", [
        'item_id' => $item->id,
    ]);

    $response->assertOk();
    $this->assertDatabaseCount('photo_items', 1);
    $this->assertDatabaseHas('photo_items', [
        'photo_id' => $photo->id,
        'item_id' => $item->id,
        'recycled' => true,
    ]);
});
