<?php

use App\Actions\Photos\GetRelevantTagShortcutAction;
use App\Models\Item;
use App\Models\Tag;
use App\Models\TagShortcut;
use App\Models\TagShortcutItem;
use App\Models\User;

it('returns null when no relevant tag shortcut exists', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $item = Item::factory()->create();

    $irrelevant = TagShortcut::factory()->create(['user_id' => $otherUser->id]);
    TagShortcutItem::factory()->create([
        'tag_shortcut_id' => $irrelevant->id,
        'item_id' => $item->id,
    ]);

    $result = app(GetRelevantTagShortcutAction::class)->run($user, $item->id);

    expect($result)->toBeNull();
});

it('returns the most recently updated relevant tag shortcut with eager-loaded relations', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $item = Item::factory()->create();
    $differentItem = Item::factory()->create();

    // Irrelevant: different user but with the same item and newer timestamp
    $otherUsersShortcut = TagShortcut::factory()->create([
        'user_id' => $otherUser->id,
        'updated_at' => now()->addMinute(),
    ]);
    TagShortcutItem::factory()->create([
        'tag_shortcut_id' => $otherUsersShortcut->id,
        'item_id' => $item->id,
    ]);

    // Relevant but older
    $older = TagShortcut::factory()->create([
        'user_id' => $user->id,
        'updated_at' => now()->subMinutes(2),
    ]);
    TagShortcutItem::factory()->create([
        'tag_shortcut_id' => $older->id,
        'item_id' => $item->id,
    ]);

    // Relevant and newer than $older
    $newer = TagShortcut::factory()->create([
        'user_id' => $user->id,
        'updated_at' => now()->subMinute(),
    ]);
    TagShortcutItem::factory()->create([
        'tag_shortcut_id' => $newer->id,
        'item_id' => $item->id,
    ]);

    // Relevant with same updated_at as $candidateA but higher id (tie-breaker by id desc)
    $candidateA = TagShortcut::factory()->create([
        'user_id' => $user->id,
        'updated_at' => now(),
    ]);
    TagShortcutItem::factory()->create([
        'tag_shortcut_id' => $candidateA->id,
        'item_id' => $item->id,
    ]);

    $candidateB = TagShortcut::factory()->create([
        'user_id' => $user->id,
        'updated_at' => now(),
    ]);

    // Add two tag shortcut items to candidateB to exercise eager loads and ordering
    $tsi1 = TagShortcutItem::factory()->create([
        'tag_shortcut_id' => $candidateB->id,
        'item_id' => $item->id,
    ]);

    $tsi2 = TagShortcutItem::factory()->create([
        'tag_shortcut_id' => $candidateB->id,
        'item_id' => $differentItem->id,
    ]);

    // attach tags to both items
    $tag1 = Tag::factory()->create();
    $tag2 = Tag::factory()->create();
    $tsi1->tags()->attach($tag1->id);
    $tsi2->tags()->attach($tag2->id);

    // Also create a relevant shortcut for the user but for a different item – should be ignored
    $differentItemShortcut = TagShortcut::factory()->create([
        'user_id' => $user->id,
        'updated_at' => now()->addMinutes(5),
    ]);
    TagShortcutItem::factory()->create([
        'tag_shortcut_id' => $differentItemShortcut->id,
        'item_id' => $differentItem->id,
    ]);

    $result = app(GetRelevantTagShortcutAction::class)->run($user, $item->id);

    // Expect tie-breaker by id: candidateB should win
    expect($result)->not()->toBeNull()
        ->and($result->id)->toBe($candidateB->id);

    // Eager loads are applied
    expect($result->relationLoaded('tagShortcutItems'))->toBeTrue();

    // Ensure each tagShortcutItem has item and tags eager loaded, and order is by id desc
    $ids = $result->tagShortcutItems->pluck('id')->all();
    $sorted = $ids;
    rsort($sorted);

    expect($ids)->toBe($sorted);

    foreach ($result->tagShortcutItems as $tsi) {
        expect($tsi->relationLoaded('item'))->toBeTrue()
            ->and($tsi->relationLoaded('tags'))->toBeTrue();
    }
});
