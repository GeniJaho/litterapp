<?php

use App\Models\Item;
use App\Models\TagShortcut;
use Illuminate\Database\Eloquent\Collection;

test('a tag shortcut belongs to many items', function (): void {
    $tagShortcut = TagShortcut::factory()->create();
    $items = Item::factory(2)->create();

    $tagShortcut->items()->attach($items->pluck('id'));

    $tagShortcut->refresh();
    expect($tagShortcut->items)->toBeInstanceOf(Collection::class)
        ->toHaveCount(2)
        ->and($tagShortcut->items->pluck('id'))
        ->toEqualCanonicalizing($items->pluck('id'));
});
