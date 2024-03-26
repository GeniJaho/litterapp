<?php

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Laravel\Jetstream\Features;
use Laravel\Jetstream\Mail\TeamInvitation;

test('team members can be invited to team', function (): void {
    Mail::fake();

    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    $response = $this->post('/teams/'.$user->currentTeam->id.'/members', [
        'email' => 'test@example.com',
        'role' => 'admin',
    ]);

    Mail::assertSent(TeamInvitation::class);

    expect($user->currentTeam->fresh()->teamInvitations)->toHaveCount(1);
})->skip(fn (): bool => ! Features::sendsTeamInvitations(), 'Team invitations not enabled.');

test('team member invitations can be cancelled', function (): void {
    Mail::fake();

    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    $invitation = $user->currentTeam->teamInvitations()->create([
        'email' => 'test@example.com',
        'role' => 'admin',
    ]);

    $response = $this->delete('/team-invitations/'.$invitation->id);

    expect($user->currentTeam->fresh()->teamInvitations)->toHaveCount(0);
})->skip(fn (): bool => ! Features::sendsTeamInvitations(), 'Team invitations not enabled.');
