<?php

namespace App\Models;

use Database\Factories\PhotoSuggestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $photo_id
 * @property int $item_id
 * @property int $item_score
 * @property int $item_count
 * @property ?int $brand_tag_id
 * @property ?int $brand_score
 * @property ?int $brand_count
 * @property ?int $content_tag_id
 * @property ?int $content_score
 * @property ?int $content_count
 * @property ?bool $is_accepted
 * @property Item $item
 * @property Photo $photo
 * @property ?Tag $brandTag
 * @property ?Tag $contentTag
 */
class PhotoSuggestion extends Model
{
    /** @use HasFactory<PhotoSuggestionFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'item_score' => 'integer',
            'item_count' => 'integer',
            'brand_score' => 'integer',
            'brand_count' => 'integer',
            'content_score' => 'integer',
            'content_count' => 'integer',
            'is_accepted' => 'boolean',
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
     * @return BelongsTo<Tag, $this>
     */
    public function brandTag(): BelongsTo
    {
        return $this->belongsTo(Tag::class, 'brand_tag_id');
    }

    /**
     * @return BelongsTo<Tag, $this>
     */
    public function contentTag(): BelongsTo
    {
        return $this->belongsTo(Tag::class, 'content_tag_id');
    }
}
