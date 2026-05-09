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
    PhotoSuggestion::factory()
        ->withPredictions([$item->id])
        ->create([
            'photo_id' => $photo1->id,
            'item_id' => $item->id,
            'item_score' => 90,
            'is_accepted' => true,
        ]);

    // Rejected suggestion
    PhotoSuggestion::factory()
        ->withPredictions([$item->id])
        ->create([
            'photo_id' => $photo2->id,
            'item_id' => $item->id,
            'item_score' => 40,
            'is_accepted' => false,
        ]);

    // Pending suggestion
    PhotoSuggestion::factory()
        ->withPredictions([$item->id])
        ->create([
            'photo_id' => $photo3->id,
            'item_id' => $item->id,
            'item_score' => 70,
            'is_accepted' => null,
        ]);

    $this->artisan('app:suggestion-metrics')
        ->assertSuccessful()
        ->expectsOutputToContain('3');
});

it('displays acceptance by score bucket', function (): void {
    $item = Item::factory()->create();

    PhotoSuggestion::factory()
        ->withPredictions([$item->id])
        ->create([
            'item_id' => $item->id,
            'item_score' => 95,
            'is_accepted' => true,
        ]);

    PhotoSuggestion::factory()
        ->withPredictions([$item->id])
        ->create([
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

    PhotoSuggestion::factory()
        ->withPredictions([$item->id])
        ->create([
            'item_id' => $item->id,
            'item_score' => 90,
            'is_accepted' => true,
        ]);

    PhotoSuggestion::factory()
        ->withPredictions([$item->id])
        ->create([
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

    PhotoSuggestion::factory()
        ->withPredictions([$item->id], [$brand->id])
        ->create([
            'item_id' => $item->id,
            'item_score' => 90,
            'is_accepted' => true,
            'brand_accepted' => true,
        ]);

    $this->artisan('app:suggestion-metrics')
        ->assertSuccessful()
        ->expectsOutputToContain('Brand Suggestion Acceptance (multi-item)');
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
        ->expectsOutputToContain('Accepted Item Rank Distribution (multi-item)')
        ->expectsOutputToContain('Multi-suggestion uplift');
});

it('applies min-score filtering to rank distribution', function (): void {
    $item1 = Item::factory()->create();
    $item2 = Item::factory()->create();

    PhotoSuggestion::factory()
        ->withPredictions([$item1->id, $item2->id])
        ->create([
            'is_accepted' => true,
            'item_score' => 95,
            'accepted_item_rank' => 1,
        ]);

    PhotoSuggestion::factory()
        ->withPredictions([$item1->id, $item2->id])
        ->create([
            'is_accepted' => true,
            'item_score' => 20,
            'accepted_item_rank' => 2,
        ]);

    $this->artisan('app:suggestion-metrics --min-score=50')
        ->assertSuccessful()
        ->expectsOutputToContain('Multi-suggestion uplift (accepted with rank data): 0% were NOT rank 1');
});

it('handles no suggestions gracefully', function (): void {
    $this->artisan('app:suggestion-metrics')
        ->assertSuccessful()
        ->expectsOutputToContain('Overview');
});
