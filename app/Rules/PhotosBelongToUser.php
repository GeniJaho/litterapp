<?php

namespace App\Rules;

use App\Models\Photo;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class PhotosBelongToUser implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
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
    }
}
