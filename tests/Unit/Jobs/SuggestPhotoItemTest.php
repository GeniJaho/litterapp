<?php

use App\Actions\Photos\ClassifiesPhoto;
use App\Actions\Photos\GetItemFromPredictionAction;
use App\DTO\PhotoItemPrediction;
use App\Jobs\SuggestPhotoItem;
use App\Models\Item;
use App\Models\Photo;
use Tests\Doubles\FakeClassifyPhotoAction;

beforeEach(function (): void {
    $this->photo = Photo::factory()->create();
    $this->item = Item::factory()->create(['name' => 'Bottle']);

    $this->fakeClassifyPhotoAction = app(FakeClassifyPhotoAction::class);
    $this->swap(ClassifiesPhoto::class, $this->fakeClassifyPhotoAction);
});

test('it creates a photo item suggestion when successful', function (): void {
    $this->fakeClassifyPhotoAction->shouldReturnPrediction(
        new PhotoItemPrediction('bottle', 0.95)
    );

    $job = new SuggestPhotoItem($this->photo);
    $result = $job->handle(
        $this->fakeClassifyPhotoAction,
        app(GetItemFromPredictionAction::class)
    );

    expect($result)->toBe(0);
    $this->assertDatabaseHas('photo_item_suggestions', [
        'photo_id' => $this->photo->id,
        'item_id' => $this->item->id,
        'score' => 0.95,
    ]);
});

test('it returns failure code when classification fails', function (): void {
    $this->fakeClassifyPhotoAction->shouldFail();

    $job = new SuggestPhotoItem($this->photo);
    $result = $job->handle(
        $this->fakeClassifyPhotoAction,
        app(GetItemFromPredictionAction::class)
    );

    expect($result)->toBe(1);
    $this->assertDatabaseMissing('photo_item_suggestions', ['photo_id' => $this->photo->id]);
});

test('it returns failure code when item not found', function (): void {
    $this->fakeClassifyPhotoAction->shouldReturnPrediction(
        new PhotoItemPrediction('unknown-item', 0.95)
    );

    $job = new SuggestPhotoItem($this->photo);
    $result = $job->handle(
        $this->fakeClassifyPhotoAction,
        app(GetItemFromPredictionAction::class)
    );

    expect($result)->toBe(1);
    $this->assertDatabaseMissing('photo_item_suggestions', ['photo_id' => $this->photo->id]);
});

test('it does not create suggestion when item already exists in photo', function (): void {
    $this->photo->items()->attach($this->item);

    $this->fakeClassifyPhotoAction->shouldReturnPrediction(
        new PhotoItemPrediction('bottle', 0.95)
    );

    $job = new SuggestPhotoItem($this->photo);
    $result = $job->handle(
        $this->fakeClassifyPhotoAction,
        app(GetItemFromPredictionAction::class)
    );

    expect($result)->toBe(0);
    $this->assertDatabaseMissing('photo_item_suggestions', [
        'photo_id' => $this->photo->id,
        'item_id' => $this->item->id,
    ]);
});
