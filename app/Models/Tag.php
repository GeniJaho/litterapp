<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tag extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * @return BelongsTo<TagType, Tag>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(TagType::class, 'tag_type_id');
    }

    /**
     * @return HasMany<PhotoItemTag>
     */
    public function photoItemTags(): HasMany
    {
        return $this->hasMany(PhotoItemTag::class);
    }
}
