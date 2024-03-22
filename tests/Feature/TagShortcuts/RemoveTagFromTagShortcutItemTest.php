<?php

use App\Models\Item;
use App\Models\TagShortcut;
use App\Models\TagShortcutItem;
use App\Models\TagShortcutItemTag;
use App\Models\Tag;
use App\Models\User;

test('a user can remove a tag from an item of a tag shortcut', function () {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->for($user)->create();
    $item = Item::factory()->create();
    $tagShortcutItem = TagShortcutItem::factory()->for($item)->for($tagShortcut)->create();
    $tag = Tag::factory()->create();
    TagShortcutItemTag::factory()->for($tagShortcutItem)->for($tag)->create();

    $response = $this->actingAs($user)->deleteJson("/user/tag-shortcut-items/{$tagShortcutItem->id}/tags/{$tag->id}");

    $response->assertOk();
    $this->assertDatabaseEmpty('tag_shortcut_item_tag');
});

test('a user can not remove a tag from an item of a tag shortcut of another user', function () {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->create();
    $item = Item::factory()->create();
    $tagShortcutItem = TagShortcutItem::factory()->for($item)->for($tagShortcut)->create();
    $tag = Tag::factory()->create();
    TagShortcutItemTag::factory()->for($tagShortcutItem)->for($tag)->create();

    $response = $this->actingAs($user)->deleteJson("/user/tag-shortcut-items/{$tagShortcutItem->id}/tags/{$tag->id}");

    $response->assertNotFound();
    $this->assertDatabaseCount('tag_shortcut_item_tag', 1);
});
