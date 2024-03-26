<?php

use App\Models\Item;
use App\Models\Tag;
use App\Models\TagShortcut;
use App\Models\TagShortcutItem;
use App\Models\TagShortcutItemTag;
use App\Models\User;

test('a user can duplicate an item and its tags on a tag shortcut', function (): void {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->for($user)->create();
    $item = Item::factory()->create();
    $tagShortcutItem = TagShortcutItem::factory()->for($item)->for($tagShortcut)->create();
    $tag = Tag::factory()->create();
    TagShortcutItemTag::factory()->for($tagShortcutItem)->for($tag)->create();

    $response = $this->actingAs($user)->postJson("/user/tag-shortcut-items/{$tagShortcutItem->id}/copy");

    $response->assertOk();
    $this->assertDatabaseCount('tag_shortcut_items', 2);
    $this->assertDatabaseCount('tag_shortcut_item_tag', 2);

    $latestTagShortcutItem = TagShortcutItem::latest('id')->first();
    $this->assertEquals($tagShortcutItem->item_id, $latestTagShortcutItem->item_id);
    $this->assertEquals($tagShortcutItem->tag_shortcut_id, $latestTagShortcutItem->tag_shortcut_id);

    $latestTagShortcutItemTag = TagShortcutItemTag::latest('id')->first();
    $this->assertEquals($latestTagShortcutItem->id, $latestTagShortcutItemTag->tag_shortcut_item_id);
    $this->assertEquals($tag->id, $latestTagShortcutItemTag->tag_id);
});

test('a user can not duplicate an item and its tags on a tag shortcut of another user', function (): void {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->create();
    $item = Item::factory()->create();
    $tagShortcutItem = TagShortcutItem::factory()->for($item)->for($tagShortcut)->create();
    $tag = Tag::factory()->create();
    TagShortcutItemTag::factory()->for($tagShortcutItem)->for($tag)->create();

    $response = $this->actingAs($user)->postJson("/user/tag-shortcut-items/{$tagShortcutItem->id}/copy");

    $response->assertNotFound();
    $this->assertDatabaseCount('tag_shortcut_items', 1);
    $this->assertDatabaseCount('tag_shortcut_item_tag', 1);
});
