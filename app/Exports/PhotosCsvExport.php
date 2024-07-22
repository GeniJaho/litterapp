<?php

namespace App\Exports;

use Generator;
use Maatwebsite\Excel\Concerns\FromGenerator;

readonly class PhotosCsvExport implements FromGenerator
{
    public function __construct(private Generator $photos)
    {
    }

    public function generator(): Generator
    {
        return $this->photos;
    }
}
