<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePhotosRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'photo' => [
                'required',
                'mimes:jpg,jpeg,png,webp,heic,heif',
                'max:20480',
                // for extra security https://securinglaravel.com/p/laravel-security-file-upload-vulnerability/comment/3666187
                'dimensions:min_width=1,min_height=1',
            ],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $photoExists = auth()->user()
                    ?->photos()
                    ->where('original_file_name', $this->photo->getClientOriginalName())
                    ->exists();

                if ($photoExists) {
                    $validator->errors()->add('photo', 'You have already uploaded this photo!');
                }
            },
        ];
    }
}
