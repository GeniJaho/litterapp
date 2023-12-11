<?php

namespace Database\Factories;

use App\Models\Tag;
use App\Models\TagType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $word = $this->faker->unique()->word();

        return [
            'tag_type_id' => TagType::factory(),
            'name' => $word,
            'slug' => Str::slug($word),
        ];
    }
}
