<?php

namespace App\DTO;

use App\Models\Photo;
use Closure;
use Spatie\LaravelData\Data;

/**
 * @property  int[]  $photo_ids
 * @property BulkItem[] $items
 */
class BulkPhotoItems extends Data
{
    /**
     * @param  int[]  $photo_ids
     * @param BulkItem[] $items
     */
    public function __construct(
        public array $photo_ids,
        public array $items = [],
    ) {
    }

    /**
     * @return array<string, string[]>
     */
    public static function rules(): array
    {
        return [
            'photo_ids' => [
                'required',
                'array',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! is_array($value)) {
                        return;
                    }

                    $photosBelongsToOthers = Photo::query()
                        ->whereIn('id', $value)
                        ->where('user_id', '!=', auth()->id())
                        ->exists();

                    if ($photosBelongsToOthers) {
                        $fail('You are not the owner of the photos.');
                    }
                },
            ],
            'photo_ids.*' => ['required', 'exists:photos,id'],
//            'items' => ['required', 'array'],
        ];
    }
}
