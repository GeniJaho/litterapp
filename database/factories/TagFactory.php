<?php

namespace Database\Factories;

use App\Models\Tag;
use App\Models\TagType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tag_type_id' => TagType::factory(),
            'name' => fake()->unique()->word(),
            'deleted_at' => null,
        ];
    }
}
