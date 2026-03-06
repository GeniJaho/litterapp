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

test('a photo has many suggestions', function (): void {
    $photo = Photo::factory()->create();
    $itemA = Item::factory()->create();
    $itemB = Item::factory()->create();

    $photo->photoSuggestions()->createMany([
        ['item_id' => $itemA->id, 'item_score' => 95, 'item_count' => 10],
        ['item_id' => $itemB->id, 'item_score' => 85, 'item_count' => 5],
    ]);

    expect($photo->photoSuggestions)->toHaveCount(2)
        ->and($photo->photoSuggestions->first()->item_score)->toBe(95);
});
