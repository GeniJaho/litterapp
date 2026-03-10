<?php

namespace App\Http\Requests\Photos;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePhotoItemRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'item_ids' => ['required', 'array'],
            'item_ids.*' => ['required', 'exists:items,id'],
            'suggestion_id' => ['nullable', 'exists:photo_suggestions,id'],
            'accepted_item_rank' => ['nullable', 'integer', 'min:1', 'max:5'],
            'brand_tag_ids' => ['nullable', 'array'],
            'brand_tag_ids.*' => ['integer', 'exists:tags,id'],
            'content_tag_ids' => ['nullable', 'array'],
            'content_tag_ids.*' => ['integer', 'exists:tags,id'],
        ];
    }
}
