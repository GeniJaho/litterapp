<?php

namespace App\Models;

use Database\Factories\TagShortcutItemTagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TagShortcutItemTag extends Pivot
{
    /** @use HasFactory<TagShortcutItemTagFactory> */
    use HasFactory;

    protected $table = 'tag_shortcut_item_tag';

    public $incrementing = true;

    /**
     * @return BelongsTo<TagShortcutItem, TagShortcutItemTag>
     */
    public function tagShortcutItem(): BelongsTo
    {
        return $this->belongsTo(TagShortcutItem::class);
    }

    /**
     * @return BelongsTo<Tag, TagShortcutItemTag>
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
