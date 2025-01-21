<?php

namespace App\Http\Requests\Photos;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

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
            function (Validator $validator): void {
                /** @var UploadedFile $photo */
                $photo = $this->photo;

                $photoExists = auth()->user()
                    ?->photos()
                    ->where('original_file_name', $photo->getClientOriginalName())
                    ->exists();

                if ($photoExists) {
                    $validator->errors()->add('photo', 'You have already uploaded this photo!');
                }
            },
        ];
    }
}
