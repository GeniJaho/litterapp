<?php

namespace App\DTO;

use Spatie\LaravelData\Data;

class PhotoItemPrediction extends Data
{
    public function __construct(
        public string $class_name,
        public float $score,
    ) {}
}
