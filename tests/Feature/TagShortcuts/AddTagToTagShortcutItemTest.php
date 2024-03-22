<?php

use App\Models\Item;
use App\Models\Tag;
use App\Models\TagShortcut;
use App\Models\TagShortcutItem;
use App\Models\User;

test('a user can add tags to an item of a tag shortcut', function () {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->for($user)->create();
    $item = Item::factory()->create();
    $tagShortcutItem = TagShortcutItem::factory()->for($item)->for($tagShortcut)->create();
    $tagA = Tag::factory()->create();
    $tagB = Tag::factory()->create();

    $response = $this->actingAs($user)->postJson("/user/tag-shortcut-items/{$tagShortcutItem->id}/tags", [
        'tag_ids' => [$tagA->id, $tagB->id],
    ]);

    $response->assertOk();
    $this->assertDatabaseCount('tag_shortcut_item_tag', 2);
    $this->assertDatabaseHas('tag_shortcut_item_tag', [
        'tag_shortcut_item_id' => $tagShortcutItem->id,
        'tag_id' => $tagA->id,
    ]);
    $this->assertDatabaseHas('tag_shortcut_item_tag', [
        'tag_shortcut_item_id' => $tagShortcutItem->id,
        'tag_id' => $tagB->id,
    ]);
});

test('the request is validated', function () {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->for($user)->create();
    $item = Item::factory()->create();
    $tagShortcutItem = TagShortcutItem::factory()->for($item)->for($tagShortcut)->create();

    $response = $this->actingAs($user)->postJson("/user/tag-shortcut-items/{$tagShortcutItem->id}/tags", [
        'tag_ids' => ['12345'],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('tag_ids.0');
    $this->assertDatabaseCount('tag_shortcut_item_tag', 0);
});

test('a user can not add a tag to an item of a tag shortcut of another user', function () {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->create();
    $item = Item::factory()->create();
    $tagShortcutItem = TagShortcutItem::factory()->for($item)->for($tagShortcut)->create();
    $tag = Tag::factory()->create();

    $response = $this->actingAs($user)->postJson("/user/tag-shortcut-items/{$tagShortcutItem->id}/tags", [
        'tag_ids' => [$tag->id],
    ]);

    $response->assertNotFound();
    $this->assertDatabaseCount('tag_shortcut_item_tag', 0);
});
