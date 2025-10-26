<?php

namespace App\Rules;

use App\Models\TagShortcut;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class TagShortcutsBelongToUser implements ValidationRule
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

        $shortcutsBelongsToOthers = TagShortcut::query()
            ->whereIn('id', $value)
            ->where('user_id', '!=', auth()->id())
            ->exists();

        if ($shortcutsBelongsToOthers) {
            $fail('You do not have these tag shortcuts.');
        }
    }
}
