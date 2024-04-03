<?php

use App\Actions\Photos\GetTagsAndItemsAction;
use App\Models\Item;
use App\Models\Tag;

it('does not include deprecated tags or items by default', function (): void {
    $tag = Tag::factory()->create();
    $deprecatedTag = Tag::factory()->create(['deleted_at' => now()]);
    $item = Item::factory()->create();
    $deprecatedItem = Item::factory()->create(['deleted_at' => now()]);

    /** @var GetTagsAndItemsAction $action */
    $action = app(GetTagsAndItemsAction::class);
    $tagsAndItems = $action->run();

    expect(collect($tagsAndItems['tags'])->flatten()->pluck('id'))
        ->toContain($tag->id)
        ->not->toContain($deprecatedTag->id)
        ->and(collect($tagsAndItems['items'])->flatten()->pluck('id'))
        ->toContain($item->id)
        ->not->toContain($deprecatedItem->id);
});

it('includes deprecated items and tags if required', function (): void {
    $tag = Tag::factory()->create();
    $deprecatedTag = Tag::factory()->create(['deleted_at' => now()]);
    $item = Item::factory()->create();
    $deprecatedItem = Item::factory()->create(['deleted_at' => now()]);

    /** @var GetTagsAndItemsAction $action */
    $action = app(GetTagsAndItemsAction::class);
    $tagsAndItems = $action->run(withTrashed: true);

    expect(collect($tagsAndItems['tags'])->flatten()->pluck('id'))
        ->toContain($tag->id)
        ->toContain($deprecatedTag->id)
        ->and(collect($tagsAndItems['items'])->flatten()->pluck('id'))
        ->toContain($item->id)
        ->toContain($deprecatedItem->id);
});
