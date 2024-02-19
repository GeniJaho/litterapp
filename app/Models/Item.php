<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property PhotoItem|null $pivot
 * @property Collection<Photo> $photos
 */
class Item extends Model
{
    use HasFactory;

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
}
