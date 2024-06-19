<?php

namespace App\Http\Requests\Groups;

use App\Models\Group;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGroupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, (ValidationRule | array<mixed> | string)>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('groups')
                    ->where('user_id', auth()->id())
                    ->ignore($this->route('group')),
            ],
        ];
    }

    public function authorize(): bool
    {
        /** @var Group|null $group */
        $group = $this->route('group');

        return auth()->id() === (int) $group?->user_id;
    }
}
