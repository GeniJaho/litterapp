<?php

use Laravel\Socialite\Facades\Socialite;

it('redirects to Twitter', function () {
    $spy = Socialite::spy();

    $this->get('/auth/twitter/redirect');

    $spy->shouldHaveReceived('driver')->with('twitter-oauth-2')->once();
});
