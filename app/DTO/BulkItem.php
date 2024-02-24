<?php

namespace App\DTO;

use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;

class BulkItem extends Data
{
    /**
     * @param int[] $tag_ids
     */
    public function __construct(
        #[Exists('items', 'id')]
        public int $id,
        public bool $picked_up,
        public bool $recycled,
        public bool $deposit,
        #[Min(1), Max(1000)]
        public int $quantity,
        #[Exists('tags', 'id')]
        public ?array $tag_ids = []
    ) {
    }

    /**
     * @return string[]
     */
    public static function messages(): array
    {
        return [
            'id.required' => 'The item #:position is required.',
            'id.exists' => 'The item #:position does not exist.',
            'picked_up.required' => 'The picked up on item #:position is required.',
            'picked_up.boolean' => 'The picked up on item #:position must be toggled on or off.',
            'recycled.required' => 'The recycled on item #:position is required.',
            'recycled.boolean' => 'The recycled on item #:position must be toggled on or off.',
            'deposit.required' => 'The deposit on item #:position is required.',
            'deposit.boolean' => 'The deposit on item #:position must be toggled on or off.',
            'quantity.required' => 'The quantity on item #:position is required.',
            'quantity.min' => 'The quantity on item #:position must be at least :min.',
            'quantity.max' => 'The quantity on item #:position may not be greater than :max.',
            'tag_ids.exists' => 'Tags on item #:position do not exist.',
        ];
    }
}
