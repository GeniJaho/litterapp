<?php

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as SocialiteUser;

it('redirects to Google', function (): void {
    $spy = Socialite::spy();

    $this->get('/auth/google/redirect');

    $spy->shouldHaveReceived('driver')->with('google')->once();
});

it('lands on the dashboard after signing in with Google', function (): void {
    fakeGoogleUser(User::factory()->create()->email);

    $this->get('/auth/google/callback')
        ->assertRedirect(RouteServiceProvider::HOME);
});

it('returns to the page the user was headed for before signing in with Google', function (): void {
    fakeGoogleUser(User::factory()->create()->email);

    $this->withSession(['url.intended' => url('/upload')]);

    $this->get('/auth/google/callback')
        ->assertRedirect(url('/upload'));
});

function fakeGoogleUser(string $email): void
{
    $socialUser = new SocialiteUser;
    $socialUser->map([
        'name' => 'Ada Lovelace',
        'email' => $email,
        'avatar' => 'https://example.com/avatar.jpg',
    ]);

    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('user')->andReturn($socialUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
}
