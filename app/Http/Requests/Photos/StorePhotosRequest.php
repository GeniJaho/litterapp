<?php

namespace App\Http\Requests\Photos;

use App\Actions\Photos\ExtractsExifFromPhoto;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class StorePhotosRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    /**
     * @var array<string, mixed>
     */
    private array $exifData = [];

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
    public function after(#[CurrentUser] User $user): array
    {
        return [
            function (Validator $validator) use ($user): void {
                /** @var UploadedFile $photo */
                $photo = $this->photo;

                $this->exifData = app(ExtractsExifFromPhoto::class)->run($photo);

                if ($this->photoExists($user, $photo)) {
                    $validator->errors()->add('photo', 'You have already uploaded this photo!');
                }
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getExifData(): array
    {
        return $this->exifData;
    }

    private function photoExists(User $user, UploadedFile $photo): bool
    {
        if (isset($this->exifData['taken_at_local'])) {
            return $user
                ->photos()
                ->where('original_file_name', $photo->getClientOriginalName())
                ->where('taken_at_local', $this->exifData['taken_at_local'])
                ->exists();
        }

        return $user
            ->photos()
            ->where('original_file_name', $photo->getClientOriginalName())
            ->exists();
    }
}
