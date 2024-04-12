<?php

use App\Models\Item;
use App\Models\TagShortcut;
use App\Models\User;

test('a user can add an item to a tag shortcut', function (): void {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->create(['user_id' => $user->id]);
    $existingItem = Item::factory()->create();
    $tagShortcut->items()->attach($existingItem);
    $item = Item::factory()->create();

    $response = $this->actingAs($user)->postJson("/user/tag-shortcuts/{$tagShortcut->id}/items", [
        'item_id' => $item->id,
    ]);

    $response->assertOk();
    $this->assertDatabaseCount('tag_shortcut_items', 2);
    $this->assertDatabaseHas('tag_shortcut_items', [
        'tag_shortcut_id' => $tagShortcut->id,
        'item_id' => $item->id,
    ]);
});

test('a user can add an item more than once to a tag shortcut', function (): void {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->create(['user_id' => $user->id]);
    $existingItem = Item::factory()->create();
    $tagShortcut->items()->attach($existingItem);

    $response = $this->actingAs($user)->postJson("/user/tag-shortcuts/{$tagShortcut->id}/items", [
        'item_id' => $existingItem->id,
    ]);

    $response->assertOk();
    $this->assertDatabaseCount('tag_shortcut_items', 2);
    $this->assertDatabaseHas('tag_shortcut_items', [
        'tag_shortcut_id' => $tagShortcut->id,
        'item_id' => $existingItem->id,
    ]);
});

test('if the user has enabled settings to pick up or recycled or deposit by default the item should have them set accordingly', function (): void {
    $user = User::factory()->create(['settings' => [
        'picked_up_by_default' => true,
        'recycled_by_default' => true,
        'deposit_by_default' => true,
    ]]);
    $tagShortcut = TagShortcut::factory()->create(['user_id' => $user->id]);
    $item = Item::factory()->create();

    $response = $this->actingAs($user)->postJson("/user/tag-shortcuts/{$tagShortcut->id}/items", [
        'item_id' => $item->id,
    ]);

    $response->assertOk();
    $this->assertDatabaseCount('tag_shortcut_items', 1);
    $this->assertDatabaseHas('tag_shortcut_items', [
        'tag_shortcut_id' => $tagShortcut->id,
        'item_id' => $item->id,
        'picked_up' => true,
        'recycled' => true,
        'deposit' => true,
    ]);
});

test('the item must exist', function (): void {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson("/user/tag-shortcuts/{$tagShortcut->id}/items", [
        'item_id' => '1',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('item_id');
});

test('the item must not be deprecated', function (): void {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->create(['user_id' => $user->id]);
    $item = Item::factory()->create(['deleted_at' => now()]);

    $response = $this->actingAs($user)->postJson("/user/tag-shortcuts/{$tagShortcut->id}/items", [
        'item_id' => $item->id,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('item_id');
});

test('a user can not add an item to another users tag shortcut', function (): void {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->create();
    $item = Item::factory()->create();

    $response = $this->actingAs($user)->postJson("/user/tag-shortcuts/{$tagShortcut->id}/items", [
        'item_id' => $item->id,
    ]);

    $response->assertNotFound();
});
