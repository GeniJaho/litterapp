<?php

namespace App\Models;

use Closure;
use Database\Factories\TagShortcutFactory;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property Collection<int, Item> $items
 * @property Collection<int, TagShortcutItem> $tagShortcutItems
 */
class TagShortcut extends Model
{
    /** @use HasFactory<TagShortcutFactory> */
    use HasFactory;

    /**
     * @return array<string, Closure>
     */
    public static function commonEagerLoads(): array
    {
        return [
            'tagShortcutItems' => fn (Builder $q) => $q
                ->with('item:id,name', 'tags:id,name')
                ->orderByDesc('id'),
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Item, $this>
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'tag_shortcut_items')
            ->withPivot(['id', 'picked_up', 'recycled', 'deposit', 'quantity'])
            ->using(TagShortcutItem::class)
            ->withTimestamps();
    }

    /**
     * @return HasMany<TagShortcutItem, $this>
     */
    public function tagShortcutItems(): HasMany
    {
        return $this->hasMany(TagShortcutItem::class);
    }
}
