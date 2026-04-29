<?php

namespace App\Http\Requests\Photos;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SharePhotoRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'expires_in' => ['nullable', 'integer', 'in:7,30,90'],
        ];
    }
}
