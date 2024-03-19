<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property Collection<Item> $items
 * @property Collection<TagShortcutItem> $tagShortcutItems
 */
class TagShortcut extends Model
{
    use HasFactory;

    /**
     * @return BelongsToMany<Item>
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'tag_shortcut_items')
            ->withPivot(['id', 'picked_up', 'recycled', 'deposit', 'quantity'])
            ->using(TagShortcutItem::class)
            ->withTimestamps();
    }

    /**
     * @return HasMany<TagShortcutItem>
     */
    public function tagShortcutItems(): HasMany
    {
        return $this->hasMany(TagShortcutItem::class);
    }
}
