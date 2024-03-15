<?php

use App\Models\Item;
use App\Models\Tag;
use App\Models\TagShortcut;
use App\Models\TagShortcutItem;
use Illuminate\Database\Eloquent\Collection;

test('a tag shortcut item belongs to many tags', function () {
    $tagShortcut = TagShortcut::factory()->create();
    $items = Item::factory()->create();
    $tagShortcutItem = TagShortcutItem::factory()->create([
        'tag_shortcut_id' => $tagShortcut->id,
        'item_id' => $items->id,
    ]);
    $tags = Tag::factory(2)->create();

    $tagShortcutItem->tags()->attach($tags->pluck('id'));

    $tagShortcutItem->refresh();
    expect($tagShortcutItem->tags)->toBeInstanceOf(Collection::class)
        ->toHaveCount(2)
        ->and($tagShortcutItem->tags->pluck('id'))
        ->toEqualCanonicalizing($tags->pluck('id'));
});
