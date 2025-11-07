<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItemSuggestion;
use App\Models\Tag;
use App\Models\TagShortcut;
use App\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

test('a user can add items to many photos at once', function (): void {
    $user = User::factory()->create();
    $photoA = Photo::factory()->create(['user_id' => $user->id]);
    $photoB = Photo::factory()->create(['user_id' => $user->id]);
    $existingItem = Item::factory()->create();
    $photoA->items()->attach($existingItem);
    $newItem = Item::factory()->create();
    $tag = Tag::factory()->create();

    $response = $this->actingAs($user)->postJson('/photos/items', [
        'photo_ids' => [$photoA->id, $photoB->id],
        'items' => [[
            'id' => $newItem->id,
            'picked_up' => true,
            'recycled' => true,
            'deposit' => true,
            'quantity' => 2,
            'tag_ids' => [$tag->id],
        ]],
    ]);

    $response->assertOk();
    assertDatabaseCount('photo_items', 3);
    assertDatabaseHas('photo_items', [
        'photo_id' => $photoA->id,
        'item_id' => $existingItem->id,
    ]);
    assertDatabaseHas('photo_items', [
        'photo_id' => $photoA->id,
        'item_id' => $newItem->id,
        'picked_up' => true,
        'recycled' => true,
        'deposit' => true,
        'quantity' => 2,
    ]);
    assertDatabaseHas('photo_items', [
        'photo_id' => $photoB->id,
        'item_id' => $newItem->id,
        'picked_up' => true,
        'recycled' => true,
        'deposit' => true,
        'quantity' => 2,
    ]);

    expect($photoA->photoItems->first()->tags()->get())
        ->toHaveCount(0);
    expect($photoA->photoItems->last()->tags()->get())
        ->toHaveCount(1)
        ->first()->id->toBe($tag->id);
    expect($photoB->photoItems->last()->tags()->get())
        ->toHaveCount(1)
        ->first()->id->toBe($tag->id);
});

test('a user can add an item more than once to their photos', function (): void {
    $user = User::factory()->create();
    $existingItem = Item::factory()->create();
    $photoA = Photo::factory()->create(['user_id' => $user->id]);
    $photoA->items()->attach($existingItem);
    $photoB = Photo::factory()->create(['user_id' => $user->id]);
    $photoB->items()->attach($existingItem);

    $response = $this->actingAs($user)->postJson('/photos/items', [
        'photo_ids' => [$photoA->id, $photoB->id],
        'items' => [[
            'id' => $existingItem->id,
            'picked_up' => true,
            'recycled' => true,
            'deposit' => true,
            'quantity' => 1,
        ]],
    ]);

    $response->assertOk();
    assertDatabaseCount('photo_items', 4);
    expect($photoA->items->count())->toBe(2);
    expect($photoB->items->count())->toBe(2);
});

test('the request is validated', function ($key, $error, $value): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $item = Item::factory()->create();
    $data = [
        'photo_ids' => [$photo->id],
        'items' => [[
            'id' => $item->id,
            'picked_up' => true,
            'recycled' => true,
            'deposit' => true,
            'quantity' => 2,
            ...[$key => $value],
        ]],
    ];

    $response = $this->actingAs($user)->postJson('/photos/items', $data);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors($error);
})->with([
    'picked up is required' => ['picked_up', 'items.0.picked_up', null],
    'picked up must be a boolean' => ['picked_up', 'items.0.picked_up', 'string'],
    'recycled is required' => ['recycled', 'items.0.recycled', null],
    'recycled must be a boolean' => ['recycled', 'items.0.recycled', 'string'],
    'deposit is required' => ['deposit', 'items.0.deposit', null],
    'deposit must be a boolean' => ['deposit', 'items.0.deposit', 'string'],
    'quantity is required' => ['quantity', 'items.0.quantity', null],
    'quantity must be an integer' => ['quantity', 'items.0.quantity', 'string'],
    'quantity must be at least 1' => ['quantity', 'items.0.quantity', 0],
    'quantity may not be greater than 1000' => ['quantity', 'items.0.quantity', 1001],
]);

test('the request items must exist', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson('/photos/items', [
        'photo_ids' => [$photo->id],
        'items' => [[
            'id' => 999,
            'picked_up' => true,
            'recycled' => true,
            'deposit' => true,
            'quantity' => 2,
        ]],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('items.0.id');
});

test('the request tags must exist', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $item = Item::factory()->create();

    $response = $this->actingAs($user)->postJson('/photos/items', [
        'photo_ids' => [$photo->id],
        'items' => [[
            'id' => $item->id,
            'picked_up' => true,
            'recycled' => true,
            'deposit' => true,
            'quantity' => 2,
            'tag_ids' => [999],
        ]],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('items.0.tag_ids');
});

test('the photo ids are validated', function ($photoIds): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $item = Item::factory()->create();
    $data = [
        'photo_ids' => $photoIds,
        'items' => [[
            'id' => $item->id,
            'picked_up' => true,
            'recycled' => true,
            'deposit' => true,
            'quantity' => 2,
        ]],
    ];

    $response = $this->actingAs($user)->postJson('/photos/items', $data);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('photo_ids');
})->with([
    'photo_ids is required' => [null],
    'photo_ids must be an array' => ['string'],
]);

test('a user can not add an item to another users photo', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();
    $item = Item::factory()->create();

    $response = $this->actingAs($user)->postJson('/photos/items', [
        'photo_ids' => [$photo->id],
        'items' => [[
            'id' => $item->id,
            'picked_up' => true,
            'recycled' => true,
            'deposit' => true,
            'quantity' => 1,
        ]],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('photo_ids');
});

test('used shortcuts usage is tracked per photo', function (): void {
    $user = User::factory()->create();
    $photoA = Photo::factory()->create(['user_id' => $user->id]);
    $photoB = Photo::factory()->create(['user_id' => $user->id]);
    $item = Item::factory()->create();

    $shortcutA = TagShortcut::factory()->create(['user_id' => $user->id, 'used_times' => 0]);
    $shortcutB = TagShortcut::factory()->create(['user_id' => $user->id, 'used_times' => 1]);

    $response = $this->actingAs($user)->postJson('/photos/items', [
        'photo_ids' => [$photoA->id, $photoB->id],
        'items' => [[
            'id' => $item->id,
            'picked_up' => true,
            'recycled' => true,
            'deposit' => true,
            'quantity' => 1,
        ]],
        'used_shortcuts' => [$shortcutA->id, $shortcutB->id],
    ]);

    $response->assertOk();

    expect($shortcutA->fresh()->used_times)->toBe(2);
    expect($shortcutB->fresh()->used_times)->toBe(3);
});

test('the used shortcuts are validated', function ($value, $errorKey): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $item = Item::factory()->create();

    $response = $this->actingAs($user)->postJson('/photos/items', [
        'photo_ids' => [$photo->id],
        'items' => [[
            'id' => $item->id,
            'picked_up' => true,
            'recycled' => true,
            'deposit' => true,
            'quantity' => 1,
        ]],
        'used_shortcuts' => $value,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors($errorKey);
})->with([
    'used_shortcuts must be an array' => ['string', 'used_shortcuts'],
    'used_shortcuts entries must exist' => [[999], 'used_shortcuts.0'],
]);

test('the used shortcuts must belong to the user', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $item = Item::factory()->create();

    $shortcutFromAnotherUser = TagShortcut::factory()->create();

    $response = $this->actingAs($user)->postJson('/photos/items', [
        'photo_ids' => [$photo->id],
        'items' => [[
            'id' => $item->id,
            'picked_up' => true,
            'recycled' => true,
            'deposit' => true,
            'quantity' => 1,
        ]],
        'used_shortcuts' => [$shortcutFromAnotherUser->id],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('used_shortcuts');
});

test('adding items to photos in bulk updates matching item suggestions', function (): void {
    $user = User::factory()->create();
    $photoA = Photo::factory()->create(['user_id' => $user->id]);
    $photoB = Photo::factory()->create(['user_id' => $user->id]);
    $photoC = Photo::factory()->create(['user_id' => $user->id]);

    $itemToAdd = Item::factory()->create();
    $otherItem = Item::factory()->create();

    // Suggestions that should be updated (is_accepted is null, matches item and photos)
    $suggestionA = PhotoItemSuggestion::factory()->create([
        'photo_id' => $photoA->id,
        'item_id' => $itemToAdd->id,
        'is_accepted' => null,
    ]);
    $suggestionB = PhotoItemSuggestion::factory()->create([
        'photo_id' => $photoB->id,
        'item_id' => $itemToAdd->id,
        'is_accepted' => null,
    ]);

    // Suggestions that should NOT be updated
    // Already accepted suggestion
    $alreadyAccepted = PhotoItemSuggestion::factory()->create([
        'photo_id' => $photoA->id,
        'item_id' => $itemToAdd->id,
        'is_accepted' => true,
    ]);
    // Already rejected suggestion
    $alreadyRejected = PhotoItemSuggestion::factory()->create([
        'photo_id' => $photoB->id,
        'item_id' => $itemToAdd->id,
        'is_accepted' => false,
    ]);
    // Different item
    $differentItem = PhotoItemSuggestion::factory()->create([
        'photo_id' => $photoA->id,
        'item_id' => $otherItem->id,
        'is_accepted' => null,
    ]);
    // Different photo (not in the bulk operation)
    $differentPhoto = PhotoItemSuggestion::factory()->create([
        'photo_id' => $photoC->id,
        'item_id' => $itemToAdd->id,
        'is_accepted' => null,
    ]);

    $response = $this->actingAs($user)->postJson('/photos/items', [
        'photo_ids' => [$photoA->id, $photoB->id],
        'items' => [[
            'id' => $itemToAdd->id,
            'picked_up' => true,
            'recycled' => true,
            'deposit' => true,
            'quantity' => 1,
        ]],
    ]);

    $response->assertOk();

    // Verify suggestions that should be updated are now accepted
    expect($suggestionA->fresh()->is_accepted)->toBeTrue();
    expect($suggestionB->fresh()->is_accepted)->toBeTrue();

    // Verify suggestions that should NOT be updated remain unchanged
    expect($alreadyAccepted->fresh()->is_accepted)->toBeTrue();
    expect($alreadyRejected->fresh()->is_accepted)->toBeFalse();
    expect($differentItem->fresh()->is_accepted)->toBeNull();
    expect($differentPhoto->fresh()->is_accepted)->toBeNull();
});
