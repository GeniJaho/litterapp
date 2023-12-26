<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\User;

test('a user can mark an item as picked up', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $existingItem = Item::factory()->create();
    $photo->items()->attach($existingItem);

    $this->assertDatabaseHas('photo_items', [
        'photo_id' => $photo->id,
        'item_id' => $existingItem->id,
        'picked_up' => false,
    ]);

    $response = $this->actingAs($user)->postJson("/photo-items/{$photo->items()->first()->pivot->id}/picked-up");

    $response->assertOk();
    $this->assertDatabaseHas('photo_items', [
        'photo_id' => $photo->id,
        'item_id' => $existingItem->id,
        'picked_up' => true,
    ]);
});

test('a user can mark an item as not picked up', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $existingItem = Item::factory()->create();
    $photo->items()->attach($existingItem, ['picked_up' => true]);

    $this->assertDatabaseHas('photo_items', [
        'photo_id' => $photo->id,
        'item_id' => $existingItem->id,
        'picked_up' => true,
    ]);

    $response = $this->actingAs($user)->postJson("/photo-items/{$photo->items()->first()->pivot->id}/picked-up");

    $response->assertOk();
    $this->assertDatabaseHas('photo_items', [
        'photo_id' => $photo->id,
        'item_id' => $existingItem->id,
        'picked_up' => false,
    ]);
});
