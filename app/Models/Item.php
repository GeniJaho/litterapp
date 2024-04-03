<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property PhotoItem|null $pivot
 * @property Collection<int, Photo> $photos
 * @property Collection<int, PhotoItem> $photoItems
 */
class Item extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @return BelongsToMany<Photo>
     */
    public function photos(): BelongsToMany
    {
        return $this->belongsToMany(Photo::class, 'photo_items')
            ->withPivot(['id', 'picked_up', 'recycled', 'deposit', 'quantity'])
            ->using(PhotoItem::class)
            ->withTimestamps();
    }

    /**
     * @return HasMany<PhotoItem>
     */
    public function photoItems(): HasMany
    {
        return $this->hasMany(PhotoItem::class);
    }
}
