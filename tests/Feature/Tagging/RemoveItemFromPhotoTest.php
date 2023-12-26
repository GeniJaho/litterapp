<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\User;

test('a user can remove an item from a photo', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $existingItem = Item::factory()->create();
    $photo->items()->attach($existingItem);

    $this->assertDatabaseCount('photo_items', 1);

    $response = $this->actingAs($user)->deleteJson("/photo-items/{$photo->items()->first()->pivot->id}");

    $response->assertOk();
    $this->assertDatabaseEmpty('photo_items');
});
