<?php

use App\Models\User;
use Laravel\Jetstream\Features;

test('teams can be created', function (): void {
    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    $response = $this->post('/teams', [
        'name' => 'Test Team',
    ]);

    expect($user->fresh()->ownedTeams)->toHaveCount(2);
    expect($user->fresh()->ownedTeams()->latest('id')->first()->name)->toEqual('Test Team');
})->skip(fn (): bool => ! Features::hasTeamFeatures(), 'Teams not enabled.');
