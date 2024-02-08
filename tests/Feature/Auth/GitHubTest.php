<?php

it('redirects to GitHub', function () {
    $this->get('/auth/github/redirect')
        ->assertRedirect();
});
