<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\User;

test('a user can update the data of an item on a photo', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $existingItem = Item::factory()->create();
    $photo->items()->attach($existingItem);

    $this->assertDatabaseHas('photo_items', [
        'photo_id' => $photo->id,
        'item_id' => $existingItem->id,
        'quantity' => 1,
        'picked_up' => false,
    ]);

    $response = $this->actingAs($user)->postJson("/photo-items/{$photo->items()->first()->pivot->id}", [
        'quantity' => 5,
        'picked_up' => true,
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('photo_items', [
        'photo_id' => $photo->id,
        'item_id' => $existingItem->id,
        'quantity' => 5,
        'picked_up' => true,
    ]);
});

test('the request is validated', function ($data, $error) {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $existingItem = Item::factory()->create();
    $photo->items()->attach($existingItem);

    $response = $this->actingAs($user)->postJson("/photo-items/{$photo->items()->first()->pivot->id}", $data);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors($error);
})->with([
    'quantity not a number' => [['quantity' => 'not-a-number'], 'quantity'],
    'quantity a float' => [['quantity' => 1.5], 'quantity'],
    'quantity zero' => [['quantity' => 0], 'quantity'],
    'quantity 1001' => [['quantity' => 1001], 'quantity'],
    'picked_up not a boolean' => [['picked_up' => 'not-a-boolean'], 'picked_up'],
]);