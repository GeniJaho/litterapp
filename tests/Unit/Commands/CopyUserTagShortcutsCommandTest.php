<?php

use App\Models\TagShortcut;
use App\Models\TagShortcutItem;
use App\Models\TagShortcutItemTag;
use App\Models\User;

it('copies all tag shortcuts from one user to another user', function (): void {
    $fromUser = User::factory()->create();
    $toUser = User::factory()->create();

    $tagShortcut1 = TagShortcut::factory()->create(['user_id' => $fromUser->id]);
    $item1 = TagShortcutItem::factory()->create(['tag_shortcut_id' => $tagShortcut1->id]);
    TagShortcutItemTag::factory()->create(['tag_shortcut_item_id' => $item1->id]);

    $tagShortcut2 = TagShortcut::factory()->create(['user_id' => $fromUser->id]);
    $item2 = TagShortcutItem::factory()->create(['tag_shortcut_id' => $tagShortcut2->id]);
    TagShortcutItemTag::factory()->create(['tag_shortcut_item_id' => $item2->id]);

    $this->artisan('app:copy-user-tag-shortcuts-command', [
        'fromUserId' => $fromUser->id,
        'toUserId' => $toUser->id,
    ]);

    $toUserShortcuts = $toUser->tagShortcuts()->with('tagShortcutItems')->get();
    expect($toUserShortcuts)->toHaveCount(2);

    $fromUserShortcuts = $fromUser->tagShortcuts()->with('tagShortcutItems')->get();
    foreach ($fromUserShortcuts as $index => $shortcut) {
        $this->assertDatabaseHas('tag_shortcuts', [
            'user_id' => $toUser->id,
            'shortcut' => $shortcut->shortcut,
        ]);

        $newShortcut = $toUserShortcuts[$index];
        expect($newShortcut->tagShortcutItems)->toHaveCount($shortcut->tagShortcutItems->count());
    }
});
