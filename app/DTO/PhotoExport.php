<?php

namespace App\DTO;

use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\Tag;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

/** @phpstan-consistent-constructor */
class PhotoExport extends Data
{
    /**
     * @param  Collection<int, array<int, mixed>>  $items
     */
    public function __construct(
        public int $id,
        public string $original_file_name,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $taken_at_local,
        public string $created_at,
        public Collection $items,
    ) {}

    public static function fromModel(Photo $photo): static
    {
        return new static(
            id: $photo->id,
            original_file_name: $photo->original_file_name,
            latitude: $photo->latitude,
            longitude: $photo->longitude,
            taken_at_local: $photo->taken_at_local,
            created_at: $photo->created_at?->toIso8601String(),
            items: $photo->photoItems->map(fn (PhotoItem $photoItem): array => [
                'name' => $photoItem->item?->name,
                'picked_up' => $photoItem->picked_up,
                'recycled' => $photoItem->recycled,
                'deposit' => $photoItem->deposit,
                'quantity' => $photoItem->quantity,
                'tags' => $photoItem->tags->map(fn (Tag $tag): array => [
                    'type' => $tag->type->name,
                    'name' => $tag->name,
                ])->all(),
            ]),
        );
    }
}
