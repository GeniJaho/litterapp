<?php

use App\Models\Item;
use App\Models\TagShortcut;
use App\Models\User;

test('a user can remove an item from a tag shortcut', function (): void {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->create(['user_id' => $user->id]);
    $existingItem = Item::factory()->create();
    $tagShortcut->items()->attach($existingItem);

    $this->assertDatabaseCount('tag_shortcut_items', 1);

    $response = $this->actingAs($user)->deleteJson("/user/tag-shortcut-items/{$tagShortcut->tagShortcutItems()->first()->id}");

    $response->assertOk();
    $this->assertDatabaseEmpty('tag_shortcut_items');
});

test('a user can not remove an item from a tag shortcut of another user', function (): void {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->create();
    $existingItem = Item::factory()->create();
    $tagShortcut->items()->attach($existingItem);

    $this->assertDatabaseCount('tag_shortcut_items', 1);

    $response = $this->actingAs($user)->deleteJson("/user/tag-shortcut-items/{$tagShortcut->tagShortcutItems()->first()->id}");

    $response->assertNotFound();
    $this->assertDatabaseCount('tag_shortcut_items', 1);
});
