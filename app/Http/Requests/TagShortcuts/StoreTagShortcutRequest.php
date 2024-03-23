<?php

namespace App\Http\Requests\TagShortcuts;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTagShortcutRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, (ValidationRule | array<mixed> | string)>
     */
    public function rules(): array
    {
        return [
            'shortcut' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tag_shortcuts')->where('user_id', auth()->id()),
            ],
        ];
    }
}
