<?php

namespace Database\Factories;

use App\Models\TagShortcut;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TagShortcut>
 */
class TagShortcutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'shortcut' => fake()->unique()->word(),
            'used_times' => 0,
        ];
    }
}
