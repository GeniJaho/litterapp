<?php

namespace App\DTO;

use Spatie\LaravelData\Data;

class PhotoFilters extends Data
{
    /**
     * @param  int[]  $item_ids
     * @param  int[]  $tag_ids
     */
    public function __construct(
        public array $item_ids = [],
        public array $tag_ids = [],
        public ?string $uploaded_from = null,
        public ?string $uploaded_until = null,
        public ?string $taken_from_local = null,
        public ?string $taken_until_local = null,
        public ?bool $is_tagged = null,
        public ?bool $has_gps = null,
        public ?bool $picked_up = null,
        public ?bool $recycled = null,
        public ?bool $deposit = null,
    ) {
        $this->item_ids = array_map(fn (int|string $id): int => $id, $this->item_ids);
        $this->tag_ids = array_map(fn (int|string $id): int => $id, $this->tag_ids);
    }

    /**
     * @return array<string, string[]>
     */
    public static function rules(): array
    {
        return [
            'item_ids' => ['nullable', 'array'],
            'item_ids.*' => ['integer', 'exists:items,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'uploaded_from' => ['nullable', 'date_format:Y-m-d\\TH:i'],
            'uploaded_until' => ['nullable', 'date_format:Y-m-d\\TH:i'],
            'taken_from_local' => ['nullable', 'date_format:Y-m-d\\TH:i'],
            'taken_until_local' => ['nullable', 'date_format:Y-m-d\\TH:i'],
            'is_tagged' => ['nullable', 'boolean'],
            'has_gps' => ['nullable', 'boolean'],
            'picked_up' => ['nullable', 'boolean'],
            'recycled' => ['nullable', 'boolean'],
            'deposit' => ['nullable', 'boolean'],
        ];
    }
}
