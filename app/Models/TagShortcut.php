<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}
