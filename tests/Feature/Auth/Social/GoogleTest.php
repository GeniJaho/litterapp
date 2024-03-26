<?php

use Laravel\Socialite\Facades\Socialite;

it('redirects to Google', function (): void {
    $spy = Socialite::spy();

    $this->get('/auth/google/redirect');

    $spy->shouldHaveReceived('driver')->with('google')->once();
});
