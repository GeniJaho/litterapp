<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PhotoItem>
 */
class PhotoItemFactory extends Factory
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
        ];
    }
}
