<?php

namespace Database\Factories;

use App\Models\Tag;
use App\Models\TagShortcutItem;
use App\Models\TagShortcutItemTag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TagShortcutItemTag>
 */
class TagShortcutItemTagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tag_shortcut_item_id' => TagShortcutItem::factory(),
            'tag_id' => Tag::factory(),
        ];
    }
}
