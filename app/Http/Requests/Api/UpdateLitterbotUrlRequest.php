<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLitterbotUrlRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'key' => ['required', 'string'],
            'url' => ['required', 'string', 'url', 'starts_with:http://,https://'],
        ];
    }
}
