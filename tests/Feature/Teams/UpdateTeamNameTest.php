<?php

use App\Models\User;
use Laravel\Jetstream\Features;

test('team names can be updated', function (): void {
    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    $response = $this->put('/teams/'.$user->currentTeam->id, [
        'name' => 'Test Team',
    ]);

    expect($user->fresh()->ownedTeams)->toHaveCount(1);
    expect($user->currentTeam->fresh()->name)->toEqual('Test Team');
})->skip(fn (): bool => ! Features::hasTeamFeatures(), 'Teams not enabled.');
