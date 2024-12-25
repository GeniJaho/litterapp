<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\User;

test('a user can remove an item from many photos at once', function (): void {
    $user = User::factory()->create();
    $photoA = Photo::factory()->create(['user_id' => $user->id]);
    $photoB = Photo::factory()->create(['user_id' => $user->id]);
    $existingItem = Item::factory()->create();
    $photoA->items()->attach($existingItem);
    $photoB->items()->attach($existingItem);

    $this->assertDatabaseCount('photo_items', 2);

    $response = $this->actingAs($user)->deleteJson('/photos/items', [
        'photo_ids' => [$photoA->id, $photoB->id],
        'item_ids' => [$existingItem->id],
    ]);

    $response->assertOk();
    $this->assertDatabaseEmpty('photo_items');
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
