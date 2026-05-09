<?php

namespace App\Models;

use Database\Factories\PhotoSuggestionFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
 * @property ?array<string, array<int, array<string, mixed>>> $predictions
 * @property ?int $accepted_item_rank
 * @property ?bool $brand_accepted
 * @property ?bool $content_accepted
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
            'predictions' => 'array',
            'accepted_item_rank' => 'integer',
            'brand_accepted' => 'boolean',
            'content_accepted' => 'boolean',
        ];
    }

    /**
     * Resolve prediction IDs to names for display.
     *
     * @return Attribute<array{items: array<int, array{id: int, name: string, confidence: int}>, brands: array<int, array{id: int, name: string, confidence: int}>, content: array<int, array{id: int, name: string, confidence: int}>}, never>
     */
    protected function predictionItems(): Attribute
    {
        return Attribute::make(get: function (): array {
            if ($this->predictions === null) {
                return ['items' => [], 'brands' => [], 'content' => []];
            }

            /** @var array<int, array{id: int, confidence: float, count?: int}> $predItems */
            $predItems = $this->predictions['items'] ?? [];
            /** @var array<int, array{id: int, confidence: float}> $predBrands */
            $predBrands = $this->predictions['brands'] ?? [];
            /** @var array<int, array{id: int, confidence: float}> $predContent */
            $predContent = $this->predictions['content'] ?? [];
            $itemIds = array_column($predItems, 'id');
            $tagIds = array_merge(array_column($predBrands, 'id'), array_column($predContent, 'id'));
            $itemNames = $itemIds !== [] ? Item::whereIn('id', $itemIds)->pluck('name', 'id') : collect();
            $tagNames = $tagIds !== [] ? Tag::whereIn('id', $tagIds)->pluck('name', 'id') : collect();
            $items = array_values(array_filter(array_map(
                fn (array $i): ?array => $itemNames->has($i['id'])
                    ? ['id' => $i['id'], 'name' => $itemNames->get($i['id']), 'confidence' => (int) round($i['confidence'] * 100)]
                    : null,
                $predItems,
            )));
            $brands = array_values(array_filter(array_map(
                fn (array $b): ?array => $tagNames->has($b['id'])
                    ? ['id' => $b['id'], 'name' => $tagNames->get($b['id']), 'confidence' => (int) round($b['confidence'] * 100)]
                    : null,
                $predBrands,
            )));
            $content = array_values(array_filter(array_map(
                fn (array $c): ?array => $tagNames->has($c['id'])
                    ? ['id' => $c['id'], 'name' => $tagNames->get($c['id']), 'confidence' => (int) round($c['confidence'] * 100)]
                    : null,
                $predContent,
            )));

            return ['items' => $items, 'brands' => $brands, 'content' => $content];
        });
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
