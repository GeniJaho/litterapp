<?php

namespace App\Http\Requests\Photos;

use App\Actions\Photos\ExtractsExifFromPhoto;
use App\Models\PhotoDetail;
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

    private string $contentHash = '';

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
                $this->contentHash = hash('sha256', $photo->getContent());

                if ($this->photoExists($user)) {
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

    public function getContentHash(): string
    {
        return $this->contentHash;
    }

    private function photoExists(User $user): bool
    {
        return PhotoDetail::query()
            ->where('user_id', $user->id)
            ->where('content_hash', $this->contentHash)
            ->exists();
    }
}
