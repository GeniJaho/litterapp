<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PhotoItem extends Pivot
{
    use HasFactory;

    protected $table = 'photo_items';

    public $incrementing = true;

    protected $casts = [
        'picked_up' => 'boolean',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }

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
