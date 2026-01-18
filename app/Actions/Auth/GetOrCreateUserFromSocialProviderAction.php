<?php

namespace App\Actions\Auth;

use App\Jobs\CopyDefaultTagShortcutsAndPhotosJob;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GetOrCreateUserFromSocialProviderAction
{
    public function run(\Laravel\Socialite\Contracts\User $socialUser): User
    {
        /** @var User $user */
        $user = User::firstOrCreate([
            'email' => $socialUser->getEmail(),
        ], [
            'name' => $socialUser->getName(),
            'email_verified_at' => now(),
            'password' => Hash::make(Str::random(20)),
            'profile_photo_path' => $socialUser->getAvatar(),
        ]);

        if ($user->email_verified_at === null) {
            $user->email_verified_at = now();
        }

        if ($user->profile_photo_path === null) {
            $user->profile_photo_path = $socialUser->getAvatar();
        }

        if ($user->password === '') {
            $user->password = Hash::make(Str::random(20));
        }

        $user->save();

        if ($user->wasRecentlyCreated) {
            CopyDefaultTagShortcutsAndPhotosJob::dispatch($user);
        }

        return $user;
    }
}
