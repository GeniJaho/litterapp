<?php

namespace App\Models;

use Database\Factories\TagShortcutItemFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property TagShortcut $tagShortcut
 * @property Collection<int, Item> $items
 * @property Collection<int, Tag> $tags
 * @property Collection<int, TagShortcutItemTag> $tagShortcutItemTags
 */
class TagShortcutItem extends Pivot
{
    /** @use HasFactory<TagShortcutItemFactory> */
    use HasFactory;

    protected $table = 'tag_shortcut_items';

    public $incrementing = true;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'picked_up' => 'boolean',
            'recycled' => 'boolean',
            'deposit' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<TagShortcut, $this>
     */
    public function tagShortcut(): BelongsTo
    {
        return $this->belongsTo(TagShortcut::class);
    }

    /**
     * @return BelongsTo<Item, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'tag_shortcut_item_tag',
            'tag_shortcut_item_id',
            'tag_id',
            'id',
            'id',
        )
            ->withPivot('id')
            ->using(TagShortcutItemTag::class)
            ->withTimestamps();
    }

    /**
     * @return HasMany<TagShortcutItemTag, $this>
     */
    public function tagShortcutItemTags(): HasMany
    {
        return $this->hasMany(TagShortcutItemTag::class, 'tag_shortcut_item_id');
    }
}
