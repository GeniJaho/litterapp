<?php

namespace App\Models;

use App\DTO\PhotoFilters;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

/**
 * @property Collection<Item> $items
 */
class Photo extends Model
{
    use HasFactory;

    protected $casts = [
        'user_id' => 'integer',
    ];

    /**
     * @return BelongsTo<User, Photo>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Item>
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'photo_items')
            ->withPivot(['id', 'picked_up', 'recycled', 'quantity'])
            ->using(PhotoItem::class)
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Tag>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'photo_tag');
    }

    /**
     * @return Attribute<callable, callable>
     */
    protected function fullPath(): Attribute
    {
        return Attribute::make(get: fn () => Storage::url($this->path));
    }

    /**
     * @param  Builder<Photo>  $query
     */
    public function scopeFilter(Builder $query, ?PhotoFilters $filters): void
    {
        if (! $filters instanceof PhotoFilters) {
            return;
        }

        $query
            ->when($filters->item_ids !== [], fn ($query) => $query
                ->whereHas('items', fn ($query) => $query
                    ->whereIn('item_id', $filters->item_ids)
                )
            )
            ->when($filters->tag_ids !== [], fn ($query) => $query
                ->whereHas('items', fn ($query) => $query
                    ->join('photo_item_tag', 'photo_items.id', '=', 'photo_item_tag.photo_item_id')
                    ->whereIn('photo_item_tag.tag_id', $filters->tag_ids)
                )
            )
            ->when($filters->uploaded_from, fn ($query) => $query->where('created_at', '>=', Carbon::parse($filters->uploaded_from)->toDateTimeString()))
            ->when($filters->uploaded_until, fn ($query) => $query->where('created_at', '<=', Carbon::parse($filters->uploaded_until)->toDateTimeString()))
            ->when($filters->taken_from_local, fn ($query) => $query->where('taken_at_local', '>=', Carbon::parse($filters->taken_from_local)->toDateTimeString()))
            ->when($filters->taken_until_local, fn ($query) => $query->where('taken_at_local', '<=', Carbon::parse($filters->taken_until_local)->toDateTimeString()))
            ->when($filters->has_gps === true, fn ($query) => $query->whereNotNull('latitude')->whereNotNull('longitude'))
            ->when($filters->has_gps === false, fn ($query) => $query->where(fn ($query) => $query->whereNull('latitude')->orWhereNull('longitude')))
            ->when($filters->is_tagged === true, fn ($query) => $query->whereHas('items'))
            ->when($filters->is_tagged === false, fn ($query) => $query->whereDoesntHave('items'));
    }
}
