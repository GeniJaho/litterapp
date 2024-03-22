<?php

namespace App\Http\Requests\TagShortcuts;

use App\Models\TagShortcut;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTagShortcutRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, (ValidationRule | array<mixed> | string)>
     */
    public function rules(): array
    {
        return [
            'shortcut' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tag_shortcuts')
                    ->where('user_id', auth()->id())
                    ->ignore($this->route('tagShortcut')),
            ],
        ];
    }

    public function authorize(): bool
    {
        /** @var TagShortcut|null $tagShortcut */
        $tagShortcut = $this->route('tagShortcut');

        return auth()->id() === $tagShortcut?->user_id;
    }
}
