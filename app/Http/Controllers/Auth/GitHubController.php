<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\GetOrCreateUserFromSocialProviderAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;

class GitHubController extends Controller
{
    public function redirect(): RedirectResponse
    {
        /** @var GithubProvider $provider */
        $provider = Socialite::driver('github');

        return $provider->scopes(['read:user'])->redirect();
    }

    public function callback(GetOrCreateUserFromSocialProviderAction $action): RedirectResponse
    {
        $socialUser = Socialite::driver('github')->user();

        $user = $action->run($socialUser);

        Auth::login($user);

        return to_route('dashboard');
    }
}
