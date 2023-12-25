<?php

namespace App\DTO;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapOutputName(SnakeCaseMapper::class)]
class UserSettings extends Data
{
    public function __construct(
        public bool $pickedUpByDefault = false,
    ) {
    }
}