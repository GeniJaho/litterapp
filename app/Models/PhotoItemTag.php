<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PhotoItemTag extends Pivot
{
    use HasFactory;

    protected $table = 'photo_item_tag';

    public $incrementing = true;

    /**
     * @return BelongsTo<PhotoItem, PhotoItemTag>
     */
    public function photoItem(): BelongsTo
    {
        return $this->belongsTo(PhotoItem::class);
    }

    /**
     * @return BelongsTo<Tag, PhotoItemTag>
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
