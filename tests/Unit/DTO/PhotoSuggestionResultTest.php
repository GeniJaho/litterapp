<?php

use App\DTO\PhotoSuggestionResult;
use App\Models\Item;
use App\Models\Tag;

it('returns flat top-1 columns and predictions', function (): void {
    $item = Item::factory()->create();
    $brand = Tag::factory()->create();
    $content = Tag::factory()->create();

    $result = new PhotoSuggestionResult(
        items: [['id' => $item->id, 'name' => $item->name, 'confidence' => 0.85, 'count' => 10]],
        brands: [['id' => $brand->id, 'name' => $brand->name, 'confidence' => 0.60, 'count' => 5]],
        content: [['id' => $content->id, 'name' => $content->name, 'confidence' => 0.40, 'count' => 3]],
    );

    $attrs = $result->toSuggestionAttributes();

    expect($attrs)
        ->not->toBeNull()
        ->and($attrs['item_id'])->toBe($item->id)
        ->and($attrs['item_score'])->toBe(85)
        ->and($attrs['item_count'])->toBe(10)
        ->and($attrs['brand_tag_id'])->toBe($brand->id)
        ->and($attrs['brand_score'])->toBe(60)
        ->and($attrs['content_tag_id'])->toBe($content->id)
        ->and($attrs['content_score'])->toBe(40)
        ->and($attrs['predictions'])->toBeArray()
        ->and($attrs['predictions']['items'])->toHaveCount(1)
        ->and($attrs['predictions']['brands'])->toHaveCount(1)
        ->and($attrs['predictions']['content'])->toHaveCount(1);
});

it('limits predictions to top 3 per category', function (): void {
    $items = Item::factory()->count(5)->create();
    $brands = Tag::factory()->count(4)->create();
    $contentTags = Tag::factory()->count(4)->create();

    $result = new PhotoSuggestionResult(
        items: $items->map(fn (Item $i, int $idx): array => [
            'id' => $i->id, 'name' => $i->name, 'confidence' => round(0.9 - $idx * 0.1, 2), 'count' => 10 - $idx,
        ])->all(),
        brands: $brands->map(fn (Tag $t, int $idx): array => [
            'id' => $t->id, 'name' => $t->name, 'confidence' => round(0.8 - $idx * 0.1, 2), 'count' => 5,
        ])->all(),
        content: $contentTags->map(fn (Tag $t, int $idx): array => [
            'id' => $t->id, 'name' => $t->name, 'confidence' => round(0.7 - $idx * 0.1, 2), 'count' => 3,
        ])->all(),
    );

    $attrs = $result->toSuggestionAttributes();

    expect($attrs['predictions']['items'])->toHaveCount(3)
        ->and($attrs['predictions']['brands'])->toHaveCount(3)
        ->and($attrs['predictions']['content'])->toHaveCount(3);
});

it('filters out invalid IDs from predictions', function (): void {
    $item = Item::factory()->create();

    $result = new PhotoSuggestionResult(
        items: [
            ['id' => $item->id, 'name' => $item->name, 'confidence' => 0.9, 'count' => 10],
            ['id' => 999999, 'name' => 'Ghost', 'confidence' => 0.5, 'count' => 2],
        ],
        brands: [['id' => 999998, 'name' => 'Bad Brand', 'confidence' => 0.6, 'count' => 1]],
        content: [['id' => 999997, 'name' => 'Bad Content', 'confidence' => 0.4, 'count' => 1]],
    );

    $attrs = $result->toSuggestionAttributes();

    expect($attrs['predictions']['items'])->toHaveCount(1)
        ->and($attrs['predictions']['items'][0]['id'])->toBe($item->id)
        ->and($attrs['predictions']['brands'])->toBeEmpty()
        ->and($attrs['predictions']['content'])->toBeEmpty()
        ->and($attrs)->not->toHaveKey('brand_tag_id')
        ->and($attrs)->not->toHaveKey('content_tag_id');
});

it('handles empty items by returning null', function (): void {
    $result = new PhotoSuggestionResult(items: [], brands: [], content: []);

    expect($result->toSuggestionAttributes())->toBeNull();
});

it('returns null when top item ID is invalid', function (): void {
    $result = new PhotoSuggestionResult(
        items: [['id' => 999999, 'name' => 'Ghost', 'confidence' => 0.95, 'count' => 10]],
        brands: [],
        content: [],
    );

    expect($result->toSuggestionAttributes())->toBeNull();
});

it('handles empty brands and content with valid items', function (): void {
    $item = Item::factory()->create();

    $result = new PhotoSuggestionResult(
        items: [['id' => $item->id, 'name' => $item->name, 'confidence' => 0.8, 'count' => 5]],
        brands: [],
        content: [],
    );

    $attrs = $result->toSuggestionAttributes();

    expect($attrs)
        ->not->toBeNull()
        ->and($attrs['item_id'])->toBe($item->id)
        ->and($attrs)->not->toHaveKey('brand_tag_id')
        ->and($attrs)->not->toHaveKey('content_tag_id')
        ->and($attrs['predictions']['brands'])->toBeEmpty()
        ->and($attrs['predictions']['content'])->toBeEmpty();
});
