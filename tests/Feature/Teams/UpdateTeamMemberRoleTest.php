<?php

use App\Models\User;
use Laravel\Jetstream\Features;

test('team member roles can be updated', function (): void {
    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    $user->currentTeam->users()->attach(
        $otherUser = User::factory()->create(), ['role' => 'admin']
    );

    $response = $this->put('/teams/'.$user->currentTeam->id.'/members/'.$otherUser->id, [
        'role' => 'editor',
    ]);

    expect($otherUser->fresh()->hasTeamRole(
        $user->currentTeam->fresh(), 'editor'
    ))->toBeTrue();
})->skip(fn (): bool => ! Features::hasTeamFeatures(), 'Teams not enabled.');

test('only team owner can update team member roles', function (): void {
    $user = User::factory()->withPersonalTeam()->create();

    $user->currentTeam->users()->attach(
        $otherUser = User::factory()->create(), ['role' => 'admin']
    );

    $this->actingAs($otherUser);

    $response = $this->put('/teams/'.$user->currentTeam->id.'/members/'.$otherUser->id, [
        'role' => 'editor',
    ]);

    expect($otherUser->fresh()->hasTeamRole(
        $user->currentTeam->fresh(), 'admin'
    ))->toBeTrue();
})->skip(fn (): bool => ! Features::hasTeamFeatures(), 'Teams not enabled.');
