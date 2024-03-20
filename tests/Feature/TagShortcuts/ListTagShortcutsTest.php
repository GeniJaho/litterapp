<?php

use App\Models\Item;
use App\Models\Tag;
use App\Models\TagShortcut;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('a user can list their tag shortcuts', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->create();
    $item = Item::factory()->create();
    $tagShortcutB = TagShortcut::factory()->create(['user_id' => $user->id, 'shortcut' => 'b']);
    $tagShortcutA = TagShortcut::factory()->create(['user_id' => $user->id, 'shortcut' => 'a']);
    $tagShortcutA->items()->attach($item, [
        'picked_up' => false,
        'recycled' => false,
        'deposit' => true,
        'quantity' => 3,
    ]);
    $tagShortcutA->tagShortcutItems()->first()->tags()->attach($tag);

    $response = $this->actingAs($user)->get(route('tag-shortcuts.index'));

    $response->assertOk();
    $response->assertInertia(fn(AssertableInertia $page) => $page
        ->component('TagShortcuts/Index')
        ->has('tagShortcuts', 2)
        ->where('tagShortcuts.0.id', $tagShortcutA->id)
        ->where('tagShortcuts.0.shortcut', 'a')
        ->where('tagShortcuts.0.tag_shortcut_items.0.item.id', $item->id)
        ->where('tagShortcuts.0.tag_shortcut_items.0.item.name', $item->name)
        ->where('tagShortcuts.0.tag_shortcut_items.0.tags.0.id', $tag->id)
        ->where('tagShortcuts.0.tag_shortcut_items.0.tags.0.name', $tag->name)
        ->etc()
    );
});
