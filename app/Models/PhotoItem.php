<?php

namespace App\Models;

use Database\Factories\PhotoItemFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property Collection<int, Tag> $tags
 * @property-read Photo $photo
 */
class PhotoItem extends Pivot
{
    /** @use HasFactory<PhotoItemFactory> */
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
     * @return BelongsTo<Item, $this>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * @return BelongsTo<Photo, $this>
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
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
