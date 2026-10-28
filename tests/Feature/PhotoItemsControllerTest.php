<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoSuggestion;
use App\Models\Tag;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->photo = Photo::factory()->for($this->user)->create();
    $this->actingAs($this->user);
});

it('accepts suggestion at rank 2 with correct item attached', function (): void {
    $item1 = Item::factory()->create();
    $item2 = Item::factory()->create();

    $suggestion = PhotoSuggestion::factory()
        ->for($this->photo)
        ->withPredictions([$item1->id, $item2->id])
        ->create();

    $this->postJson("/photos/{$this->photo->id}/items", [
        'item_ids' => [$item2->id],
        'suggestion_id' => $suggestion->id,
        'brand_tag_ids' => [],
        'content_tag_ids' => [],
    ])->assertSuccessful();

    $suggestion->refresh();

    expect($suggestion->is_accepted)->toBeTrue()
        ->and($this->photo->items()->where('item_id', $item2->id)->exists())->toBeTrue();
});

it('applies brand tags from an accepted suggestion', function (): void {
    $item = Item::factory()->create();
    $brand = Tag::factory()->create();

    $suggestion = PhotoSuggestion::factory()
        ->for($this->photo)
        ->withPredictions([$item->id], [$brand->id])
        ->create();

    $this->postJson("/photos/{$this->photo->id}/items", [
        'item_ids' => [$item->id],
        'suggestion_id' => $suggestion->id,
        'brand_tag_ids' => [$brand->id],
        'content_tag_ids' => [],
    ])->assertSuccessful();

    $photoItem = $this->photo->photoItems()->where('item_id', $item->id)->first();
    expect($photoItem->tags()->where('tag_id', $brand->id)->exists())->toBeTrue();
});

it('does not apply brand tags when none are selected', function (): void {
    $item = Item::factory()->create();
    $brand = Tag::factory()->create();

    $suggestion = PhotoSuggestion::factory()
        ->for($this->photo)
        ->withPredictions([$item->id], [$brand->id])
        ->create();

    $this->postJson("/photos/{$this->photo->id}/items", [
        'item_ids' => [$item->id],
        'suggestion_id' => $suggestion->id,
        'brand_tag_ids' => [],
        'content_tag_ids' => [],
    ])->assertSuccessful();

    $photoItem = $this->photo->photoItems()->where('item_id', $item->id)->first();
    expect($photoItem->tags()->exists())->toBeFalse();
});

it('applies content tags from an accepted suggestion', function (): void {
    $item = Item::factory()->create();
    $contentTag = Tag::factory()->create();

    $suggestion = PhotoSuggestion::factory()
        ->for($this->photo)
        ->withPredictions([$item->id], [], [$contentTag->id])
        ->create();

    $this->postJson("/photos/{$this->photo->id}/items", [
        'item_ids' => [$item->id],
        'suggestion_id' => $suggestion->id,
        'brand_tag_ids' => [],
        'content_tag_ids' => [$contentTag->id],
    ])->assertSuccessful();

    $photoItem = $this->photo->photoItems()->where('item_id', $item->id)->first();
    expect($photoItem->tags()->where('tag_id', $contentTag->id)->exists())->toBeTrue();
});

it('accepts a different item than top-1 correctly', function (): void {
    $item1 = Item::factory()->create();
    $item2 = Item::factory()->create();
    $item3 = Item::factory()->create();
    $brand = Tag::factory()->create();

    $suggestion = PhotoSuggestion::factory()
        ->for($this->photo)
        ->withPredictions([$item1->id, $item2->id, $item3->id], [$brand->id])
        ->create();

    $this->postJson("/photos/{$this->photo->id}/items", [
        'item_ids' => [$item3->id],
        'suggestion_id' => $suggestion->id,
        'brand_tag_ids' => [$brand->id],
        'content_tag_ids' => [],
    ])->assertSuccessful();

    $suggestion->refresh();

    expect($suggestion->is_accepted)->toBeTrue();

    // Brand tag should be on item3's photo_item, not item1
    $photoItem = $this->photo->photoItems()->where('item_id', $item3->id)->first();
    expect($photoItem->tags()->where('tag_id', $brand->id)->exists())->toBeTrue();
});
