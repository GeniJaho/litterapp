<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\GetOrCreateUserFromSocialProviderAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')
            ->scopes(['read:user'])
            ->redirect();
    }

    public function callback(GetOrCreateUserFromSocialProviderAction $action): RedirectResponse
    {
        $socialUser = Socialite::driver('google')->user();

        $user = $action->run($socialUser);

        Auth::login($user);

        return to_route('dashboard');
    }
}
