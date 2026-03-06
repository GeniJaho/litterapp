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
     * validating that referenced IDs exist in the database.
     *
     * @return ?array<string, int|null>
     */
    public function toSuggestionAttributes(): ?array
    {
        if ($this->items === []) {
            return null;
        }

        $topItem = $this->items[0];

        if (! Item::query()->where('id', $topItem['id'])->exists()) {
            return null;
        }

        $data = [
            'item_id' => $topItem['id'],
            'item_score' => (int) round($topItem['confidence'] * 100),
            'item_count' => $topItem['count'],
        ];

        if ($this->brands !== []) {
            $topBrand = $this->brands[0];
            if (Tag::query()->where('id', $topBrand['id'])->exists()) {
                $data['brand_tag_id'] = $topBrand['id'];
                $data['brand_score'] = (int) round($topBrand['confidence'] * 100);
                $data['brand_count'] = $topBrand['count'];
            }
        }

        if ($this->content !== []) {
            $topContent = $this->content[0];
            if (Tag::query()->where('id', $topContent['id'])->exists()) {
                $data['content_tag_id'] = $topContent['id'];
                $data['content_score'] = (int) round($topContent['confidence'] * 100);
                $data['content_count'] = $topContent['count'];
            }
        }

        return $data;
    }
}
