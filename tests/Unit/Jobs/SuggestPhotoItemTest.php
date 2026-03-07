<?php

use App\Actions\Photos\SuggestsPhotoTags;
use App\DTO\PhotoSuggestionResult;
use App\Jobs\SuggestPhotoItem;
use App\Models\Item;
use App\Models\Photo;
use App\Models\Tag;
use Tests\Doubles\FakeSuggestPhotoTagsAction;

beforeEach(function (): void {
    $this->photo = Photo::factory()->create();
    $this->item = Item::factory()->create();

    $this->fakeAction = app(FakeSuggestPhotoTagsAction::class);
    $this->swap(SuggestsPhotoTags::class, $this->fakeAction);
});

test('it creates a photo suggestion when successful', function (): void {
    $this->fakeAction->shouldReturnResult(new PhotoSuggestionResult(
        items: [['id' => $this->item->id, 'name' => $this->item->name, 'confidence' => 0.95, 'count' => 10]],
        brands: [],
        content: [],
    ));

    $job = new SuggestPhotoItem($this->photo);
    $result = $job->handle($this->fakeAction);

    expect($result)->toBe(0);
    $this->assertDatabaseHas('photo_suggestions', [
        'photo_id' => $this->photo->id,
        'item_id' => $this->item->id,
        'item_score' => 95,
        'item_count' => 10,
    ]);
});

test('it creates a photo suggestion with brand and content tags', function (): void {
    $brandTag = Tag::factory()->create();
    $contentTag = Tag::factory()->create();

    $this->fakeAction->shouldReturnResult(new PhotoSuggestionResult(
        items: [['id' => $this->item->id, 'name' => $this->item->name, 'confidence' => 0.85, 'count' => 8]],
        brands: [['id' => $brandTag->id, 'name' => $brandTag->name, 'confidence' => 0.70, 'count' => 5]],
        content: [['id' => $contentTag->id, 'name' => $contentTag->name, 'confidence' => 0.60, 'count' => 3]],
    ));

    $job = new SuggestPhotoItem($this->photo);
    $result = $job->handle($this->fakeAction);

    expect($result)->toBe(0);
    $this->assertDatabaseHas('photo_suggestions', [
        'photo_id' => $this->photo->id,
        'item_id' => $this->item->id,
        'item_score' => 85,
        'item_count' => 8,
        'brand_tag_id' => $brandTag->id,
        'brand_score' => 70,
        'brand_count' => 5,
        'content_tag_id' => $contentTag->id,
        'content_score' => 60,
        'content_count' => 3,
    ]);
});

test('it returns failure code when action fails', function (): void {
    $this->fakeAction->shouldFail();

    $job = new SuggestPhotoItem($this->photo);
    $result = $job->handle($this->fakeAction);

    expect($result)->toBe(1);
    $this->assertDatabaseMissing('photo_suggestions', ['photo_id' => $this->photo->id]);
});

test('it returns failure code when no items returned', function (): void {
    $this->fakeAction->shouldReturnResult(new PhotoSuggestionResult(
        items: [],
        brands: [],
        content: [],
    ));

    $job = new SuggestPhotoItem($this->photo);
    $result = $job->handle($this->fakeAction);

    expect($result)->toBe(1);
    $this->assertDatabaseMissing('photo_suggestions', ['photo_id' => $this->photo->id]);
});

test('it does not create suggestion when item already exists on photo', function (): void {
    $this->photo->items()->attach($this->item);

    $this->fakeAction->shouldReturnResult(new PhotoSuggestionResult(
        items: [['id' => $this->item->id, 'name' => $this->item->name, 'confidence' => 0.95, 'count' => 10]],
        brands: [],
        content: [],
    ));

    $job = new SuggestPhotoItem($this->photo);
    $result = $job->handle($this->fakeAction);

    expect($result)->toBe(0);
    $this->assertDatabaseMissing('photo_suggestions', [
        'photo_id' => $this->photo->id,
        'item_id' => $this->item->id,
    ]);
});
