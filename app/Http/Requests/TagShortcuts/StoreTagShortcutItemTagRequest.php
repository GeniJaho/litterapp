<?php

namespace App\Http\Requests\TagShortcuts;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTagShortcutItemTagRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'required|integer|exists:tags,id,deleted_at,null',
        ];
    }
}
