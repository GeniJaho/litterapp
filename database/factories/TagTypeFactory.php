<?php

namespace Database\Factories;

use App\Models\TagType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TagType>
 */
class TagTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word,
            'slug' => fake()->unique()->slug,
        ];
    }
}
