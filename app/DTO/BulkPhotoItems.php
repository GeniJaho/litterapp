<?php

namespace App\DTO;

use App\Rules\PhotosBelongToUser;
use App\Rules\TagShortcutsBelongToUser;
use Spatie\LaravelData\Data;

class BulkPhotoItems extends Data
{
    /**
     * @param  int[]  $photo_ids
     * @param  BulkItem[]  $items
     * @param  int[]  $used_shortcuts
     */
    public function __construct(
        public array $photo_ids,
        public array $items = [],
        public array $used_shortcuts = [],
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
            'used_shortcuts' => ['array', new TagShortcutsBelongToUser],
            'used_shortcuts.*' => ['required', 'exists:tag_shortcuts,id'],
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
