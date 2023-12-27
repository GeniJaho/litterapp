<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property Collection<Item> $items
 */
class Photo extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<User, \App\Models\Photo>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'photo_items')
            ->withPivot(['id', 'picked_up'])
            ->using(PhotoItem::class)
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Tag>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'photo_tag');
    }

    protected function fullPath(): Attribute
    {
        return Attribute::make(get: fn () => '/storage/'.$this->path);
    }
}
