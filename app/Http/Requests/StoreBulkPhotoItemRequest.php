<?php

namespace App\Http\Requests;

use App\Models\Photo;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBulkPhotoItemRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<int, mixed>|string>
     */
    public function rules(): array
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
                        $fail("You are not the owner of the photos.");
                    }
                },
            ],
            'photo_ids.*' => ['required', 'exists:photos,id'],
            'picked_up' => 'required|boolean',
            'recycled' => 'required|boolean',
            'deposit' => 'required|boolean',
            'quantity' => 'required|integer|min:1|max:1000',
        ];
    }
}
