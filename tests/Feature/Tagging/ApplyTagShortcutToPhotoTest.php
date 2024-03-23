<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\TagShortcut;
use App\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

test('a user can apply a tag shortcut to a photo', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $item = Item::factory()->create();
    $tag = Tag::factory()->create();
    $tagShortcut = TagShortcut::factory()->create(['user_id' => $user->id]);
    $tagShortcut->items()->attach($item, [
        'picked_up' => true,
        'recycled' => true,
        'deposit' => false,
        'quantity' => 2,
    ]);
    $tagShortcut->tagShortcutItems()->first()->tags()->attach($tag);
    assertDatabaseCount('photo_items', 0);

    $response = $this->actingAs($user)->postJson("/photos/$photo->id/tag-shortcuts/$tagShortcut->id");

    $response->assertOk();
    assertDatabaseCount('photo_items', 1);
    assertDatabaseHas('photo_items', [
        'photo_id' => $photo->id,
        'item_id' => $item->id,
        'picked_up' => true,
        'recycled' => true,
        'deposit' => false,
        'quantity' => 2,
    ]);
    expect($photo->photoItems->last()->tags()->get())
        ->toHaveCount(1)
        ->first()->id->toBe($tag->id);
});

test('a user can not apply a tag shortcut to another users photo', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();
    $tagShortcut = TagShortcut::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson("/photos/$photo->id/tag-shortcuts/$tagShortcut->id");

    $response->assertNotfound();
});

test('a user can not apply a tag shortcut that belongs to another user', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $tagShortcut = TagShortcut::factory()->create();

    $response = $this->actingAs($user)->postJson("/photos/$photo->id/tag-shortcuts/$tagShortcut->id");

    $response->assertNotfound();
});
