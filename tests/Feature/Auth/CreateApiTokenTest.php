<?php

use App\Models\User;
use Laravel\Jetstream\Features;

test('api tokens can be created', function () {
    if (Features::hasTeamFeatures()) {
        $this->actingAs($user = User::factory()->withPersonalTeam()->create());
    } else {
        $this->actingAs($user = User::factory()->create());
    }

    $response = $this->post('/user/api-tokens', [
        'name' => 'Test Token',
        'permissions' => [],
    ]);

    expect($user->fresh()->tokens)->toHaveCount(1);
    expect($user->fresh()->tokens->first())->name->toEqual('Test Token');
})->skip(function () {
    return ! Features::hasApiFeatures();
}, 'API support is not enabled.');
