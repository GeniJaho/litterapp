<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\GetOrCreateUserFromSocialProviderAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;

class GoogleController extends Controller
{
    public function redirect(): RedirectResponse
    {
        /** @var GoogleProvider $provider */
        $provider = Socialite::driver('google');

        return $provider->redirect();
    }

    public function callback(GetOrCreateUserFromSocialProviderAction $action): RedirectResponse
    {
        $socialUser = Socialite::driver('google')->user();

        $user = $action->run($socialUser);

        Auth::login($user);

        return to_route('home');
    }
}
