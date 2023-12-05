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

    public function photoItem(): BelongsTo
    {
        return $this->belongsTo(PhotoItem::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
