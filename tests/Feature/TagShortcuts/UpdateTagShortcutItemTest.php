<?php

use App\Models\Item;
use App\Models\TagShortcut;
use App\Models\User;

test('a user can update the data of an item on a tag shortcut', function () {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->create(['user_id' => $user->id]);
    $existingItem = Item::factory()->create();
    $tagShortcut->items()->attach($existingItem);

    $this->assertDatabaseHas('tag_shortcut_items', [
        'tag_shortcut_id' => $tagShortcut->id,
        'item_id' => $existingItem->id,
        'quantity' => 1,
        'picked_up' => false,
        'recycled' => false,
        'deposit' => false,
    ]);

    $response = $this->actingAs($user)->postJson("/user/tag-shortcut-items/{$tagShortcut->tagShortcutItems()->first()->id}", [
        'quantity' => 5,
        'picked_up' => true,
        'recycled' => true,
        'deposit' => true,
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('tag_shortcut_items', [
        'tag_shortcut_id' => $tagShortcut->id,
        'item_id' => $existingItem->id,
        'quantity' => 5,
        'picked_up' => true,
        'recycled' => true,
        'deposit' => true,
    ]);
});

test('the request is validated', function ($data, $error) {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->create(['user_id' => $user->id]);
    $existingItem = Item::factory()->create();
    $tagShortcut->items()->attach($existingItem);

    $response = $this->actingAs($user)->postJson("/user/tag-shortcut-items/{$tagShortcut->tagShortcutItems()->first()->id}", $data);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors($error);
})->with([
    'quantity not a number' => [['quantity' => 'not-a-number'], 'quantity'],
    'quantity a float' => [['quantity' => 1.5], 'quantity'],
    'quantity zero' => [['quantity' => 0], 'quantity'],
    'quantity 1001' => [['quantity' => 1001], 'quantity'],
    'picked_up not a boolean' => [['picked_up' => 'not-a-boolean'], 'picked_up'],
    'recycled not a boolean' => [['recycled' => 'not-a-boolean'], 'recycled'],
    'deposit not a boolean' => [['deposit' => 'not-a-boolean'], 'deposit'],
]);

test('a user can not update an item on a tag shortcut of another user', function () {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->create();
    $existingItem = Item::factory()->create();
    $tagShortcut->items()->attach($existingItem);

    $response = $this->actingAs($user)->postJson("/user/tag-shortcut-items/{$tagShortcut->tagShortcutItems()->first()->id}", [
        'quantity' => 5,
        'picked_up' => true,
        'recycled' => true,
        'deposit' => true,
    ]);

    $response->assertNotFound();
});
