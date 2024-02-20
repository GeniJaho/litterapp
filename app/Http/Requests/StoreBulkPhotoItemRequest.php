<?php

namespace App\Http\Requests;

use App\Models\Photo;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBulkPhotoItemRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [

        ];
    }
}
