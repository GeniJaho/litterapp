<?php

namespace App\Http\Requests\Groups;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreGroupPhotosRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'photo_ids' => ['required', 'array'],
            'photo_ids.*' => [
                'required',
                'exists:photos,id',
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
                $photoIds = $this->input('photo_ids', []);
                /** @var User $user */
                $user = auth()->user();

                if ($user->photos()->whereIn('id', $photoIds)->count() !== count($photoIds)) {
                    $validator->errors()->add('photo_ids', 'One or more photos do not belong to you.');
                }
            },
        ];
    }
}
