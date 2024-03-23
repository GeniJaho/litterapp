<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\TagShortcut;
use App\Models\TagShortcutItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TagShortcutItem>
 */
class TagShortcutItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tag_shortcut_id' => TagShortcut::factory(),
            'item_id' => Item::factory(),
        ];
    }
}
