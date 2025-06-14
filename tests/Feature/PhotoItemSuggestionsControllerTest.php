<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItemSuggestion;
use App\Models\User;

test('it rejects a photo item suggestion', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $suggestion = PhotoItemSuggestion::factory()->for($photo)->for($item)->create([
        'is_accepted' => null,
    ]);

    $response = $this->postJson(route('photo-item-suggestions.reject', $suggestion));

    $response->assertOk();

    expect($suggestion->fresh()->is_accepted)->toBeFalse();
});

test('it returns 404 when suggestion does not belong to user', function (): void {
    $this->actingAs($user = User::factory()->create());
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->for($otherUser)->create();
    $item = Item::factory()->create();
    $suggestion = PhotoItemSuggestion::factory()->for($photo)->for($item)->create();

    $response = $this->postJson(route('photo-item-suggestions.reject', $suggestion));

    $response->assertNotFound();

    expect($suggestion->fresh()->is_accepted)->toBeNull();
});

test('it requires authentication', function (): void {
    $photo = Photo::factory()->create();
    $item = Item::factory()->create();
    $suggestion = PhotoItemSuggestion::factory()->for($photo)->for($item)->create();

    $response = $this->postJson(route('photo-item-suggestions.reject', $suggestion));

    $response->assertUnauthorized();

    expect($suggestion->fresh()->is_accepted)->toBeNull();
});
