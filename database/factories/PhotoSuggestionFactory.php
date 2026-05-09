<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoSuggestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PhotoSuggestion>
 */
class PhotoSuggestionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'photo_id' => Photo::factory(),
            'item_id' => Item::factory(),
            'item_score' => fake()->numberBetween(0, 100),
            'item_count' => fake()->numberBetween(1, 50),
            'is_accepted' => null,
        ];
    }

    /**
     * @param  array<int, int>  $itemIds
     * @param  array<int, int>  $brandTagIds
     * @param  array<int, int>  $contentTagIds
     */
    public function withPredictions(array $itemIds = [], array $brandTagIds = [], array $contentTagIds = []): static
    {
        return $this->state(function (array $attributes) use ($itemIds, $brandTagIds, $contentTagIds): array {
            $confidence = 0.5;
            $items = [];
            foreach ($itemIds as $id) {
                $items[] = ['id' => $id, 'confidence' => round($confidence, 3), 'count' => fake()->numberBetween(1, 20)];
                $confidence = max(0.05, $confidence - 0.15);
            }

            $confidence = 0.4;
            $brands = [];
            foreach ($brandTagIds as $id) {
                $brands[] = ['id' => $id, 'confidence' => round($confidence, 3)];
                $confidence = max(0.05, $confidence - 0.1);
            }

            $confidence = 0.4;
            $content = [];
            foreach ($contentTagIds as $id) {
                $content[] = ['id' => $id, 'confidence' => round($confidence, 3)];
                $confidence = max(0.05, $confidence - 0.1);
            }

            $topItem = $items !== [] ? $items[0] : null;

            return [
                'item_id' => $topItem ? $topItem['id'] : $attributes['item_id'],
                'item_score' => $topItem ? (int) round($topItem['confidence'] * 100) : $attributes['item_score'],
                'item_count' => $topItem ? $topItem['count'] : $attributes['item_count'],
                'predictions' => [
                    'items' => $items,
                    'brands' => $brands,
                    'content' => $content,
                ],
            ];
        });
    }
}
