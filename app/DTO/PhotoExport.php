<?php

namespace App\DTO;

use App\Models\Photo;
use App\Models\PhotoItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class PhotoExport extends Data
{
    public function __construct(
        public int $id,
        public string $original_file_name,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $taken_at_local,
        public Carbon $created_at,
        public Collection $items,
    ) {
    }

    public static function fromModel(Photo $photo): static
    {
        return new static(
            id: $photo->id,
            original_file_name: $photo->original_file_name,
            latitude: $photo->latitude,
            longitude: $photo->longitude,
            taken_at_local: $photo->taken_at_local,
            created_at: $photo->created_at,
            items: $photo->photoItems->map(fn (PhotoItem $photoItem) => [
                'name' => $photoItem->item->name,
                'picked_up' => $photoItem->picked_up,
                'recycled' => $photoItem->recycled,
                'deposit' => $photoItem->deposit,
                'quantity' => $photoItem->quantity,
                'tags' => $photoItem->tags->pluck('name')->toArray(),
            ]),
        );
    }
}
