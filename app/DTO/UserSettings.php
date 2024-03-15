<?php

namespace App\DTO;

use Spatie\LaravelData\Data;

class UserSettings extends Data
{
    public function __construct(
        public bool $picked_up_by_default = false,
        public bool $recycled_by_default = false,
        public bool $deposit_by_default = false,
        public ?PhotoFilters $photo_filters = null,
        public int $per_page = 12,
    ) {
    }
}
