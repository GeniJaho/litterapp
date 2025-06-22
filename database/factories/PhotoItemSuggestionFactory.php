<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItemSuggestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PhotoItemSuggestion>
 */
class PhotoItemSuggestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'photo_id' => Photo::factory(),
            'item_id' => Item::factory(),
            'score' => fake()->randomFloat(2, 0, 1),
            'is_accepted' => null,
        ];
    }
}
