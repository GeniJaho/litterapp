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
        'accepted_item_rank' => 2,
        'brand_tag_ids' => [],
        'content_tag_ids' => [],
    ])->assertSuccessful();

    $suggestion->refresh();

    expect($suggestion->is_accepted)->toBeTrue()
        ->and($suggestion->accepted_item_rank)->toBe(2)
        ->and($this->photo->items()->where('item_id', $item2->id)->exists())->toBeTrue();
});

it('applies brand tags and sets brand_accepted to true', function (): void {
    $item = Item::factory()->create();
    $brand = Tag::factory()->create();

    $suggestion = PhotoSuggestion::factory()
        ->for($this->photo)
        ->withPredictions([$item->id], [$brand->id])
        ->create();

    $this->postJson("/photos/{$this->photo->id}/items", [
        'item_ids' => [$item->id],
        'suggestion_id' => $suggestion->id,
        'accepted_item_rank' => 1,
        'brand_tag_ids' => [$brand->id],
        'content_tag_ids' => [],
    ])->assertSuccessful();

    $suggestion->refresh();

    expect($suggestion->brand_accepted)->toBeTrue()
        ->and($suggestion->content_accepted)->toBeFalse();

    $photoItem = $this->photo->photoItems()->where('item_id', $item->id)->first();
    expect($photoItem->tags()->where('tag_id', $brand->id)->exists())->toBeTrue();
});

it('sets brand_accepted to false when brand_tag_ids is empty', function (): void {
    $item = Item::factory()->create();
    $brand = Tag::factory()->create();

    $suggestion = PhotoSuggestion::factory()
        ->for($this->photo)
        ->withPredictions([$item->id], [$brand->id])
        ->create();

    $this->postJson("/photos/{$this->photo->id}/items", [
        'item_ids' => [$item->id],
        'suggestion_id' => $suggestion->id,
        'accepted_item_rank' => 1,
        'brand_tag_ids' => [],
        'content_tag_ids' => [],
    ])->assertSuccessful();

    expect($suggestion->refresh()->brand_accepted)->toBeFalse();
});

it('applies content tags and sets content_accepted to true', function (): void {
    $item = Item::factory()->create();
    $contentTag = Tag::factory()->create();

    $suggestion = PhotoSuggestion::factory()
        ->for($this->photo)
        ->withPredictions([$item->id], [], [$contentTag->id])
        ->create();

    $this->postJson("/photos/{$this->photo->id}/items", [
        'item_ids' => [$item->id],
        'suggestion_id' => $suggestion->id,
        'accepted_item_rank' => 1,
        'brand_tag_ids' => [],
        'content_tag_ids' => [$contentTag->id],
    ])->assertSuccessful();

    $suggestion->refresh();

    expect($suggestion->content_accepted)->toBeTrue();

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
        'accepted_item_rank' => 3,
        'brand_tag_ids' => [$brand->id],
        'content_tag_ids' => [],
    ])->assertSuccessful();

    $suggestion->refresh();

    expect($suggestion->is_accepted)->toBeTrue()
        ->and($suggestion->accepted_item_rank)->toBe(3)
        ->and($suggestion->brand_accepted)->toBeTrue();

    // Brand tag should be on item3's photo_item, not item1
    $photoItem = $this->photo->photoItems()->where('item_id', $item3->id)->first();
    expect($photoItem->tags()->where('tag_id', $brand->id)->exists())->toBeTrue();
});
