<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePhotoItemRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'picked_up' => 'nullable|boolean',
            'recycled' => 'nullable|boolean',
            'deposit' => 'nullable|boolean',
            'quantity' => 'nullable|integer|min:1|max:1000',
        ];
    }
}
