<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\GetOrCreateUserFromSocialProviderAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\TwitterProvider;

class TwitterController extends Controller
{
    public function redirect(): RedirectResponse
    {
        /** @var TwitterProvider $provider */
        $provider = Socialite::driver('twitter-oauth-2');

        return $provider->setScopes(['users.read'])->redirect();
    }

    public function callback(GetOrCreateUserFromSocialProviderAction $action): RedirectResponse
    {
        $socialUser = Socialite::driver('twitter-oauth-2')->user();

        $user = $action->run($socialUser);

        Auth::login($user);

        return to_route('home');
    }
}
