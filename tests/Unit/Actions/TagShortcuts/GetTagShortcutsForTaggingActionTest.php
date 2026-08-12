<?php

use App\Actions\TagShortcuts\GetTagShortcutsForTaggingAction;
use App\Models\Item;
use App\Models\Tag;
use App\Models\TagShortcut;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('it returns only the fields the tagging ui reads', function (): void {
    $user = User::factory()->create();
    $item = Item::factory()->create(['name' => 'Cigarette Butt']);
    $tag = Tag::factory()->create(['name' => 'Cellulose Acetate Fiber']);

    $tagShortcut = TagShortcut::factory()->create([
        'user_id' => $user->id,
        'shortcut' => '01 cigarette butt',
    ]);
    $tagShortcut->items()->attach($item, [
        'picked_up' => true,
        'recycled' => false,
        'deposit' => true,
        'quantity' => 3,
    ]);
    $shortcutItem = $tagShortcut->tagShortcutItems()->first();
    $shortcutItem->tags()->attach($tag);

    $shortcuts = app(GetTagShortcutsForTaggingAction::class)->run($user);

    expect($shortcuts)->toBe([
        [
            'id' => $tagShortcut->id,
            'shortcut' => '01 cigarette butt',
            'tag_shortcut_items' => [
                [
                    'id' => $shortcutItem->id,
                    'quantity' => 3,
                    'picked_up' => true,
                    'recycled' => false,
                    'deposit' => true,
                    'item' => [
                        'id' => $item->id,
                        'name' => 'Cigarette Butt',
                    ],
                    'tags' => [
                        [
                            'id' => $tag->id,
                            'name' => 'Cellulose Acetate Fiber',
                        ],
                    ],
                ],
            ],
        ],
    ]);
});

test('it excludes shortcuts without items and shortcuts belonging to other users', function (): void {
    $user = User::factory()->create();
    $item = Item::factory()->create();

    TagShortcut::factory()->create(['user_id' => $user->id, 'shortcut' => 'empty']);

    $withItems = TagShortcut::factory()->create(['user_id' => $user->id, 'shortcut' => 'has items']);
    $withItems->items()->attach($item);

    $otherUsersShortcut = TagShortcut::factory()->create(['shortcut' => 'someone else']);
    $otherUsersShortcut->items()->attach($item);

    $shortcuts = app(GetTagShortcutsForTaggingAction::class)->run($user);

    expect($shortcuts)->toHaveCount(1)
        ->and($shortcuts[0]['id'])->toBe($withItems->id);
});

test('it orders shortcuts by name and items by most recently added', function (): void {
    $user = User::factory()->create();
    $items = Item::factory(2)->create();

    $second = TagShortcut::factory()->create(['user_id' => $user->id, 'shortcut' => 'b shortcut']);
    $second->items()->attach($items->first());

    $first = TagShortcut::factory()->create(['user_id' => $user->id, 'shortcut' => 'a shortcut']);
    $first->items()->attach($items->first());
    $first->items()->attach($items->last());

    $shortcuts = app(GetTagShortcutsForTaggingAction::class)->run($user);

    expect(array_column($shortcuts, 'shortcut'))->toBe(['a shortcut', 'b shortcut'])
        ->and(array_column($shortcuts[0]['tag_shortcut_items'], 'id'))
        ->toBe($first->tagShortcutItems()->orderByDesc('id')->pluck('id')->all());
});

test('it does not load the shortcut collection through eloquent', function (): void {
    $user = User::factory()->create();
    $item = Item::factory()->create();
    $tag = Tag::factory()->create();

    $tagShortcut = TagShortcut::factory()->create(['user_id' => $user->id]);
    $tagShortcut->items()->attach($item);
    $tagShortcut->tagShortcutItems()->first()->tags()->attach($tag);

    $queries = 0;
    DB::listen(function () use (&$queries): void {
        $queries++;
    });

    app(GetTagShortcutsForTaggingAction::class)->run($user);

    expect($queries)->toBe(3);
});
