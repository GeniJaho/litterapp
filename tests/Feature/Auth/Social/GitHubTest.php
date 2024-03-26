<?php

use Laravel\Socialite\Facades\Socialite;

it('redirects to GitHub', function (): void {
    $spy = Socialite::spy();

    $this->get('/auth/github/redirect');

    $spy->shouldHaveReceived('driver')->with('github')->once();
});
