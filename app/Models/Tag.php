<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tag extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function type(): BelongsTo
    {
        return $this->belongsTo(TagType::class, 'tag_type_id');
    }
}
