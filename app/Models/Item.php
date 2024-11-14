<?php

namespace App\Models;

use Database\Factories\ItemFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property PhotoItem|null $pivot
 * @property Collection<int, Photo> $photos
 * @property Collection<int, PhotoItem> $photoItems
 */
class Item extends Model
{
    /** @use HasFactory<ItemFactory> */
    use HasFactory;

    /**
     * @return BelongsToMany<Photo, $this>
     */
    public function photos(): BelongsToMany
    {
        return $this->belongsToMany(Photo::class, 'photo_items')
            ->withPivot(['id', 'picked_up', 'recycled', 'deposit', 'quantity'])
            ->using(PhotoItem::class)
            ->withTimestamps();
    }

    /**
     * @return HasMany<PhotoItem, $this>
     */
    public function photoItems(): HasMany
    {
        return $this->hasMany(PhotoItem::class);
    }
}
