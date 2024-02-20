<?php

namespace App\DTO;

use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;

class BulkItem extends Data
{
    public function __construct(
        #[Exists('items', 'id')]
        public int $id,
        public bool $picked_up,
        public bool $recycled,
        public bool $deposit,
        #[Min(1), Max(1000)]
        public int $quantity,
        public array $tag_ids = [],
    ) {
    }
}
