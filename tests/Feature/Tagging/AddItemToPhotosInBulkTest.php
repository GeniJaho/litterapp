<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

test('a user can add items to many photos at once', function () {
    $user = User::factory()->create();
    $photoA = Photo::factory()->create(['user_id' => $user->id]);
    $photoB = Photo::factory()->create(['user_id' => $user->id]);
    $existingItem = Item::factory()->create();
    $photoA->items()->attach($existingItem);
    $newItem = Item::factory()->create();

    $response = $this->actingAs($user)->postJson("/photos/items", [
        'photo_ids' => [$photoA->id, $photoB->id],
        'items' => [
            'id' => $newItem->id,
            'picked_up' => true,
            'recycled' => true,
            'deposit' => true,
            'quantity' => 2,
        ]
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
});

test('a user can add an item more than once to their photos', function () {
    $user = User::factory()->create();
    $existingItem = Item::factory()->create();
    $photoA = Photo::factory()->create(['user_id' => $user->id]);
    $photoA->items()->attach($existingItem);
    $photoB = Photo::factory()->create(['user_id' => $user->id]);
    $photoB->items()->attach($existingItem);

    $response = $this->actingAs($user)->postJson("/photos/items", [
        'photo_ids' => [$photoA->id, $photoB->id],
        'items' => [
            'id' => $existingItem->id,
            'picked_up' => true,
            'recycled' => true,
            'deposit' => true,
            'quantity' => 1,
        ]
    ]);

    $response->assertOk();
    assertDatabaseCount('photo_items', 4);
    expect($photoA->items->count())->toBe(2);
    expect($photoB->items->count())->toBe(2);
});

test('the request is validated', function ($key, $value) {
    $user = User::factory()->create();
    $item = Item::factory()->create();
    $data = [
        'photo_ids' => [1],
        'items' => [
            'id' => $item->id,
            'picked_up' => true,
            'recycled' => true,
            'deposit' => true,
            'quantity' => 2,
            ...[$key => $value],
        ],
    ];

    $response = $this->actingAs($user)->postJson("/photos/items", $data);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors($key);
})->with([
    'photo_ids is required' => ['photo_ids', null],
    'photo_ids must be an array' => ['photo_ids', 'string'],
    'picked up is required' => ['picked_up', null],
    'picked up must be a boolean' => ['picked_up', 'string'],
    'recycled is required' => ['recycled', null],
    'recycled must be a boolean' => ['recycled', 'string'],
    'deposit is required' => ['deposit', null],
    'deposit must be a boolean' => ['deposit', 'string'],
    'quantity is required' => ['quantity', null],
    'quantity must be an integer' => ['quantity', 'string'],
    'quantity must be at least 1' => ['quantity', 0],
    'quantity may not be greater than 1000' => ['quantity', 1001],
]);

test('the request items and tags must exist', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();

    $response = $this->actingAs($user)->postJson("/photos/items", [
        'photo_ids' => [$photo->id],
        'items' => [
            'id' => 999,
            'picked_up' => true,
            'recycled' => true,
            'deposit' => true,
            'quantity' => 2,
        ],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('items.0.id');
});

test('a user can not add an item to another users photo', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();
    $item = Item::factory()->create();

    $response = $this->actingAs($user)->postJson("/photos/items", [
        'photo_ids' => [$photo->id],
        'items' => [
            'id' => $item->id,
            'picked_up' => true,
            'recycled' => true,
            'deposit' => true,
            'quantity' => 1,
        ],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('photo_ids');
});
