<?php

namespace App\DTO;

use App\Rules\PhotosBelongToUser;
use Spatie\LaravelData\Data;

class BulkPhotoItems extends Data
{
    /**
     * @param  int[]  $photo_ids
     * @param  BulkItem[]  $items
     */
    public function __construct(
        public array $photo_ids,
        public array $items = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'photo_ids' => [
                'required',
                'array',
                new PhotosBelongToUser,
            ],
            'photo_ids.*' => ['required', 'exists:photos,id'],
        ];
    }

    /**
     * @return string[]
     */
    public static function messages(): array
    {
        return [
            'photo_ids.required' => 'You must select at least one photo.',
            'photo_ids.*.required' => 'You must select at least one photo.',
            'photo_ids.*.exists' => 'The selected photo #:position does not exist.',
        ];
    }
}
