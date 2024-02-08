<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\GetOrCreateUserFromSocialProviderAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GitHubController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('github')
            ->scopes(['read:user'])
            ->redirect();
    }

    public function callback(GetOrCreateUserFromSocialProviderAction $action): RedirectResponse
    {
        $socialUser = Socialite::driver('github')->user();

        $user = $action->run($socialUser);

        Auth::login($user);

        return to_route('dashboard');
    }
}
