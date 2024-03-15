<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property Collection<Tag> $tags
 * @property Photo $photo
 */
class PhotoItem extends Pivot
{
    use HasFactory;

    protected $table = 'photo_items';

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
     * @return BelongsTo<Item, PhotoItem>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * @return BelongsTo<Photo, PhotoItem>
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }

    /**
     * @return BelongsToMany<Tag>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'photo_item_tag',
            'photo_item_id',
            'tag_id',
            'id',
            'id',
        )
            ->withPivot('id')
            ->using(PhotoItemTag::class)
            ->withTimestamps();
    }
}
