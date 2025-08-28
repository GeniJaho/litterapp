<?php

namespace App\DTO;

use Spatie\LaravelData\Data;

class UserSettings extends Data
{
    public function __construct(
        public bool $picked_up_by_default = true,
        public bool $recycled_by_default = false,
        public bool $deposit_by_default = false,
        public bool $litterbot_enabled = false,
        public ?PhotoFilters $photo_filters = null,
        public int $per_page = 25,
        public string $sort_column = 'id',
        public string $sort_direction = 'desc',
    ) {}

    public function getValidPerPage(): int
    {
        return in_array($this->per_page, [25, 50, 100, 200])
            ? $this->per_page
            : 25;
    }
}
