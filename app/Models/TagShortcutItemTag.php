<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TagShortcutItemTag extends Pivot
{
    use HasFactory;

    protected $table = 'tag_shortcut_item_tag';

    public $incrementing = true;

    public function tagShortcutItem(): BelongsTo
    {
        return $this->belongsTo(TagShortcutItem::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
