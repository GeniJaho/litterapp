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
}
