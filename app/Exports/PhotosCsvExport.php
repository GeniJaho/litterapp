<?php

namespace App\Exports;

use App\DTO\PhotoExport;
use Generator;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;

readonly class PhotosCsvExport implements FromGenerator, WithHeadings
{
    use Exportable;

    /**
     * @param  Generator<PhotoExport>  $photos
     */
    public function __construct(private Generator $photos)
    {
    }

    public function generator(): Generator
    {
        return $this->photos;
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return [
            'ID',
            'Original File Name',
            'Latitude',
            'Longitude',
            'Taken At Local',
            'Created At',
            'Items',
        ];
    }
}
