<?php

namespace App\DTO;

use App\Models\Item;
use App\Models\Tag;
use Spatie\LaravelData\Data;

class PhotoSuggestionResult extends Data
{
    /**
     * @param  array<int, array{id: int, name: string, confidence: float, count: int}>  $items
     * @param  array<int, array{id: int, name: string, confidence: float, count: int}>  $brands
     * @param  array<int, array{id: int, name: string, confidence: float, count: int}>  $content
     */
    public function __construct(
        public array $items,
        public array $brands,
        public array $content,
    ) {}

    /**
     * Build the attributes for a PhotoSuggestion row from the top-1 item/brand/content,
     * plus the full predictions JSON, validating that referenced IDs exist in the database.
     *
     * @return ?array<string, mixed>
     */
    public function toSuggestionAttributes(): ?array
    {
        if ($this->items === []) {
            return null;
        }

        $topItem = $this->items[0];

        // Collect all item IDs (up to 5) and validate in batch
        $allItemIds = array_column(array_slice($this->items, 0, 5), 'id');
        $validItemIds = Item::whereIn('id', $allItemIds)->pluck('id')->all();

        if (! in_array($topItem['id'], $validItemIds, true)) {
            return null;
        }

        // Collect all tag IDs and validate in batch
        $brandSlice = array_slice($this->brands, 0, 3);
        $contentSlice = array_slice($this->content, 0, 3);
        $allTagIds = array_merge(
            array_column($brandSlice, 'id'),
            array_column($contentSlice, 'id'),
        );
        $validTagIds = $allTagIds !== []
            ? Tag::whereIn('id', $allTagIds)->pluck('id')->all()
            : [];

        // Build flat top-1 columns
        $data = [
            'item_id' => $topItem['id'],
            'item_score' => (int) round($topItem['confidence'] * 100),
            'item_count' => $topItem['count'],
        ];

        if ($brandSlice !== []) {
            $topBrand = $brandSlice[0];
            if (in_array($topBrand['id'], $validTagIds, true)) {
                $data['brand_tag_id'] = $topBrand['id'];
                $data['brand_score'] = (int) round($topBrand['confidence'] * 100);
                $data['brand_count'] = $topBrand['count'];
            }
        }

        if ($contentSlice !== []) {
            $topContent = $contentSlice[0];
            if (in_array($topContent['id'], $validTagIds, true)) {
                $data['content_tag_id'] = $topContent['id'];
                $data['content_score'] = (int) round($topContent['confidence'] * 100);
                $data['content_count'] = $topContent['count'];
            }
        }

        // Build predictions JSON with only valid IDs, limited to top 3 per category
        $predictionItems = array_values(array_filter(
            array_map(
                fn (array $i): ?array => in_array($i['id'], $validItemIds, true)
                    ? ['id' => $i['id'], 'confidence' => $i['confidence'], 'count' => $i['count']]
                    : null,
                array_slice($this->items, 0, 3),
            ),
        ));

        $predictionBrands = array_values(array_filter(
            array_map(
                fn (array $b): ?array => in_array($b['id'], $validTagIds, true)
                    ? ['id' => $b['id'], 'confidence' => $b['confidence']]
                    : null,
                $brandSlice,
            ),
        ));

        $predictionContent = array_values(array_filter(
            array_map(
                fn (array $c): ?array => in_array($c['id'], $validTagIds, true)
                    ? ['id' => $c['id'], 'confidence' => $c['confidence']]
                    : null,
                $contentSlice,
            ),
        ));

        $data['predictions'] = [
            'items' => $predictionItems,
            'brands' => $predictionBrands,
            'content' => $predictionContent,
        ];

        return $data;
    }
}
