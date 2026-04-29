<?php

namespace App\Rules;

use App\Models\Photo;
use App\Models\User;
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

        /** @var User $user */
        $user = auth()->user();

        if ($user->is_admin) {
            return;
        }

        $photosBelongsToOthers = Photo::query()
            ->whereIn('id', $value)
            ->where('user_id', '!=', $user->id)
            ->exists();

        if ($photosBelongsToOthers) {
            $fail('You are not the owner of the photos.');
        }
    }
}
