<?php

use App\Models\TagShortcut;
use App\Models\TagShortcutItem;
use App\Models\TagShortcutItemTag;
use App\Models\User;

it('copies a tag shortcut from a user to another user or users', function (): void {
    $tagShortcut = TagShortcut::factory()->create();
    $tagShortcutItem = TagShortcutItem::factory()->create([
        'tag_shortcut_id' => $tagShortcut->id,
    ]);
    $tagShortcutItemTag = TagShortcutItemTag::factory()->create([
        'tag_shortcut_item_id' => $tagShortcutItem->id,
    ]);
    $to = User::factory()->create();
    $tagShortcut->user->tagShortcuts()->save($tagShortcut);
    $tagShortcut->tagShortcutItems()->save($tagShortcutItem);
    $tagShortcutItem->tagShortcutItemTags()->save($tagShortcutItemTag);

    $this->artisan('app:copy-tag-shortcut-command', [
        'tagShortcutId' => $tagShortcut->id,
        '--to' => $to->id,
    ]);

    $this->assertDatabaseHas('tag_shortcuts', [
        'user_id' => $to->id,
        'shortcut' => $tagShortcut->shortcut,
    ]);
    $this->assertDatabaseHas('tag_shortcut_items', [
        'tag_shortcut_id' => $to->tagShortcuts->first()->id,
    ]);
    $this->assertDatabaseHas('tag_shortcut_item_tag', [
        'tag_shortcut_item_id' => $to->tagShortcuts->first()->tagShortcutItems->first()->id,
    ]);
});
