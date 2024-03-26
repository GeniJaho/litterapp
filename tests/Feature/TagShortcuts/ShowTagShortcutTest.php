<?php

use App\Models\Item;
use App\Models\Tag;
use App\Models\TagShortcut;
use App\Models\TagShortcutItemTag;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

test('a user can see a tag shortcut', function (): void {
    $this->actingAs($user = User::factory()->create());
    $tagShortcut = TagShortcut::factory()->for($user)->create();
    $item = Item::factory()->create();
    $tagShortcut->items()->sync($item);
    $tag = Tag::factory()->create();
    TagShortcutItemTag::create([
        'tag_shortcut_item_id' => $tagShortcut->tagShortcutItems()->first()->id,
        'tag_id' => $tag->id,
    ]);

    $response = $this->getJson(route('tag-shortcuts.show', $tagShortcut));

    $response->assertOk();
    $response->assertJson(fn (AssertableJson $json): AssertableJson => $json
        ->where('tagShortcut.id', $tagShortcut->id)
        ->has('tagShortcut.tag_shortcut_items', 1)
        ->where('tagShortcut.tag_shortcut_items.0.item.id', $item->id)
        ->where('tagShortcut.tag_shortcut_items.0.item.name', $item->name)
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

test('a user can not see another users tag shortcut', function (): void {
    $this->actingAs($user = User::factory()->create());
    $otherUser = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->for($otherUser)->create();

    $response = $this->getJson(route('tag-shortcuts.show', $tagShortcut));

    $response->assertNotFound();
});
