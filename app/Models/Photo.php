<?php

namespace App\Models;

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
            ->withPivot(['id', 'picked_up', 'recycled', 'deposit', 'quantity'])
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
}
