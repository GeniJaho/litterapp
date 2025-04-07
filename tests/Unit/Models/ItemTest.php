<?php

use App\Models\Item;
use App\Models\ItemType;

test('an item has a type', function (): void {
    $itemType = ItemType::factory()->create();
    $item = Item::factory()->create(['item_type_id' => $itemType->id]);

    expect($item->type)->toBeInstanceOf(ItemType::class)
        ->and($item->type->id)->toBe($itemType->id);
});
