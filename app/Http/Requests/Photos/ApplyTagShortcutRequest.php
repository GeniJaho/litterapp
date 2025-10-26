<?php

namespace App\Http\Requests\Photos;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ApplyTagShortcutRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'suggestion_id' => ['nullable', 'exists:photo_item_suggestions,id'],
        ];
    }
}
