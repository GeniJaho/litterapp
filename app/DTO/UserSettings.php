<?php

namespace App\DTO;

use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class UserSettings extends Data
{
    public function __construct(
        #[Required, BooleanType]
        public bool $picked_up_by_default = false,
    ) {
    }
}