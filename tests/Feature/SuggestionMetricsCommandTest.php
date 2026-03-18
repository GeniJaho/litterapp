<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoSuggestion;
use App\Models\Tag;

it('displays suggestion metrics', function (): void {
    $photo1 = Photo::factory()->create();
    $photo2 = Photo::factory()->create();
    $photo3 = Photo::factory()->create();
    $item = Item::factory()->create();

    // Accepted suggestion
    PhotoSuggestion::factory()->create([
        'photo_id' => $photo1->id,
        'item_id' => $item->id,
        'item_score' => 90,
        'is_accepted' => true,
    ]);

    // Rejected suggestion
    PhotoSuggestion::factory()->create([
        'photo_id' => $photo2->id,
        'item_id' => $item->id,
        'item_score' => 40,
        'is_accepted' => false,
    ]);

    // Pending suggestion
    PhotoSuggestion::factory()->create([
        'photo_id' => $photo3->id,
        'item_id' => $item->id,
        'item_score' => 70,
        'is_accepted' => null,
    ]);

    $this->artisan('app:suggestion-metrics')
        ->assertSuccessful()
        ->expectsOutputToContain('3')
        ->expectsOutputToContain('Overview');
});

it('displays acceptance by score bucket', function (): void {
    $item = Item::factory()->create();

    PhotoSuggestion::factory()->create([
        'item_id' => $item->id,
        'item_score' => 95,
        'is_accepted' => true,
    ]);

    PhotoSuggestion::factory()->create([
        'item_id' => $item->id,
        'item_score' => 30,
        'is_accepted' => false,
    ]);

    $this->artisan('app:suggestion-metrics')
        ->assertSuccessful()
        ->expectsOutputToContain('Score Range');
});

it('filters by minimum score', function (): void {
    $item = Item::factory()->create();

    PhotoSuggestion::factory()->create([
        'item_id' => $item->id,
        'item_score' => 90,
        'is_accepted' => true,
    ]);

    PhotoSuggestion::factory()->create([
        'item_id' => $item->id,
        'item_score' => 20,
        'is_accepted' => false,
    ]);

    $this->artisan('app:suggestion-metrics --min-score=50')
        ->assertSuccessful()
        ->expectsOutputToContain('min score: 50')
        ->expectsOutputToContain('1 / 1 (100%)');
});

it('shows brand acceptance when brand tags exist', function (): void {
    $item = Item::factory()->create();
    $brand = Tag::factory()->create();
    $photo = Photo::factory()->create();

    PhotoSuggestion::factory()->create([
        'photo_id' => $photo->id,
        'item_id' => $item->id,
        'item_score' => 90,
        'brand_tag_id' => $brand->id,
        'brand_score' => 60,
        'brand_count' => 5,
        'is_accepted' => true,
    ]);

    // Attach the item and brand tag to the photo
    $photo->items()->attach($item->id);
    $photoItem = $photo->photoItems()->where('item_id', $item->id)->first();
    $photoItem->tags()->attach($brand->id);

    $this->artisan('app:suggestion-metrics')
        ->assertSuccessful()
        ->expectsOutputToContain('Brand Suggestion Acceptance');
});

it('displays rank distribution for multi-item suggestions', function (): void {
    $item1 = Item::factory()->create();
    $item2 = Item::factory()->create();

    PhotoSuggestion::factory()
        ->withPredictions([$item1->id, $item2->id])
        ->create([
            'is_accepted' => true,
            'accepted_item_rank' => 1,
        ]);

    PhotoSuggestion::factory()
        ->withPredictions([$item1->id, $item2->id])
        ->create([
            'is_accepted' => true,
            'accepted_item_rank' => 2,
        ]);

    PhotoSuggestion::factory()
        ->withPredictions([$item1->id, $item2->id])
        ->create([
            'is_accepted' => true,
            'accepted_item_rank' => 2,
        ]);

    $this->artisan('app:suggestion-metrics')
        ->assertSuccessful()
        ->expectsOutputToContain('Accepted Item Rank Distribution')
        ->expectsOutputToContain('Multi-suggestion uplift');
});

it('handles no suggestions gracefully', function (): void {
    $this->artisan('app:suggestion-metrics')
        ->assertSuccessful()
        ->expectsOutputToContain('Overview');
});
