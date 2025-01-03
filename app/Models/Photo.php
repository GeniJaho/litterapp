<?php

namespace App\Models;

use App\DTO\PhotoFilters;
use Carbon\Carbon;
use Database\Factories\PhotoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * @property Collection<int, Item> $items
 * @property Collection<int, PhotoItem> $photoItems
 */
class Photo extends Model
{
    /** @use HasFactory<PhotoFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Item, $this>
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'photo_items')
            ->withPivot(['id', 'picked_up', 'recycled', 'deposit', 'quantity'])
            ->using(PhotoItem::class)
            ->withTimestamps();
    }

    /**
     * @return HasMany<PhotoItem, $this>
     */
    public function photoItems(): HasMany
    {
        return $this->hasMany(PhotoItem::class);
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

        $photoItemProperties = array_filter([
            'picked_up' => $filters->picked_up,
            'recycled' => $filters->recycled,
            'deposit' => $filters->deposit,
        ], fn (?bool $value): bool => $value !== null);

        $query
            ->when($filters->item_ids !== [], fn (Builder $query) => $query
                ->whereHas('items', fn (Builder $query) => $query
                    ->whereIn('item_id', $filters->item_ids)
                )
            )
            ->when($filters->tag_ids !== [], fn (Builder $query) => $query
                ->whereHas('items', fn (Builder $query) => $query
                    ->join('photo_item_tag', 'photo_items.id', '=', 'photo_item_tag.photo_item_id')
                    ->whereIn('photo_item_tag.tag_id', $filters->tag_ids)
                )
            )
            ->when($filters->uploaded_from, fn (Builder $query) => $query->where('created_at', '>=', Carbon::parse($filters->uploaded_from)->toDateTimeString()))
            ->when($filters->uploaded_until, fn (Builder $query) => $query->where('created_at', '<=', Carbon::parse($filters->uploaded_until)->toDateTimeString()))
            ->when($filters->taken_from_local, fn (Builder $query) => $query->where('taken_at_local', '>=', Carbon::parse($filters->taken_from_local)->toDateTimeString()))
            ->when($filters->taken_until_local, fn (Builder $query) => $query->where('taken_at_local', '<=', Carbon::parse($filters->taken_until_local)->toDateTimeString()))
            ->when($filters->has_gps === true, fn (Builder $query) => $query->whereNotNull('latitude')->whereNotNull('longitude'))
            ->when($filters->has_gps === false, fn (Builder $query) => $query->where(fn (Builder $query) => $query->whereNull('latitude')->orWhereNull('longitude')))
            ->when($filters->is_tagged === true, fn (Builder $query) => $query->whereHas('items'))
            ->when($filters->is_tagged === false, fn (Builder $query) => $query->whereDoesntHave('items'))
            ->when($photoItemProperties !== [], fn (Builder $query) => $query->whereHas('photoItems', fn (Builder $query) => $query->where($photoItemProperties)));
    }
}
