<?php

namespace App\Models;

use Database\Factories\PhotoItemSuggestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $item_id
 * @property int $photo_id
 * @property float $score
 * @property bool $is_accepted
 * @property Item $item
 * @property Photo $photo
 */
class PhotoItemSuggestion extends Model
{
    /** @use HasFactory<PhotoItemSuggestionFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'score' => 'float',
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
}
