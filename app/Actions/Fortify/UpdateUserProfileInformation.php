<?php

namespace App\Actions\Fortify;

use App\Jobs\MinifyProfilePhoto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:15360'], // 15MB
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            /** @var UploadedFile $photo */
            $photo = $input['photo'];
            $user->updateProfilePhoto($photo);

            MinifyProfilePhoto::dispatch($user);
        }

        if ($input['email'] !== $user->email) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
