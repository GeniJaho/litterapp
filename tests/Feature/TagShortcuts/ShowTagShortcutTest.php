<?php

use App\Models\Item;
use App\Models\Tag;
use App\Models\TagShortcut;
use App\Models\TagShortcutItemTag;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

test('a user can see a tag shortcut', function () {
    $this->actingAs($user = User::factory()->create());
    $tagShortcut = TagShortcut::factory()->for($user)->create();
    $items = Item::factory()->create();
    $tagShortcut->items()->sync($items);
    $tag = Tag::factory()->create();
    TagShortcutItemTag::create([
        'tag_shortcut_item_id' => $tagShortcut->tagShortcutItems()->first()->id,
        'tag_id' => $tag->id,
    ]);

    $response = $this->getJson(route('tag-shortcuts.show', $tagShortcut));

    $response->assertOk();
    $response->assertJson(fn (AssertableJson $json) => $json
        ->where('tagShortcut.id', $tagShortcut->id)
        ->has('tagShortcut.tag_shortcut_items', 1)
        ->where('tagShortcut.tag_shortcut_items.0.item.id', $items->first()->id)
        ->where('tagShortcut.tag_shortcut_items.0.item.name', $items->first()->name)
        ->has('tagShortcut.tag_shortcut_items.0.tags', 1)
        ->where('tagShortcut.tag_shortcut_items.0.tags.0.id', $tag->id)
        ->where('tagShortcut.tag_shortcut_items.0.tags.0.name', $tag->name)
        ->has('tagShortcut.tag_shortcut_items.0.picked_up')
        ->has('tagShortcut.tag_shortcut_items.0.recycled')
        ->has('tagShortcut.tag_shortcut_items.0.deposit')
        ->has('tagShortcut.tag_shortcut_items.0.quantity')
        ->etc()
    );
});

test('a user can not see another users tag shortcut', function () {
    $this->actingAs($user = User::factory()->create());
    $otherUser = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->for($otherUser)->create();

    $response = $this->getJson(route('tag-shortcuts.show', $tagShortcut));

    $response->assertNotFound();
});
