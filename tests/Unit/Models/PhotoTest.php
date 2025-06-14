<?php

use App\Models\Item;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

test('a photo has a full path', function (): void {
    Storage::fake(config('filesystems.default'));

    $photo = Photo::factory()->create([
        'path' => 'photos/photo.jpg',
    ]);

    expect($photo->full_path)->toBe(Storage::url('photos/photo.jpg'));
});

test('a photo has many item suggestions', function (): void {
    $photo = Photo::factory()->create();
    $itemA = Item::factory()->create();
    $itemB = Item::factory()->create();

    $photo->photoItemSuggestions()->createMany([
        ['item_id' => $itemA->id, 'score' => 0.95],
        ['item_id' => $itemB->id, 'score' => 0.85],
    ]);

    expect($photo->photoItemSuggestions)->toHaveCount(2)
        ->and($photo->photoItemSuggestions->first()->score)->toBe(0.95);
});
