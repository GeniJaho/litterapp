<?php

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

it('backfills consent timestamp for legacy consenting users without a timestamp', function (): void {
    Carbon::setTestNow('2026-03-05 12:34:56');

    $legacyConsentingUser = User::factory()->create();
    $alreadyTimestampedUser = User::factory()->create();
    $legacyNonConsentingUser = User::factory()->create();

    DB::table('users')->where('id', $legacyConsentingUser->id)->update([
        'settings' => json_encode([
            'consent_to_training' => true,
        ]),
    ]);
    DB::table('users')->where('id', $alreadyTimestampedUser->id)->update([
        'settings' => json_encode([
            'consent_to_training' => true,
            'consent_to_training_at' => '2025-01-01T00:00:00+00:00',
        ]),
    ]);
    DB::table('users')->where('id', $legacyNonConsentingUser->id)->update([
        'settings' => json_encode([
            'consent_to_training' => false,
        ]),
    ]);

    $this->artisan('ml:backfill-consent-training-timestamps')
        ->expectsOutputToContain('Updated 1 user')
        ->assertExitCode(0);

    $legacySettings = json_decode((string) DB::table('users')->where('id', $legacyConsentingUser->id)->value('settings'), true);
    $alreadySettings = json_decode((string) DB::table('users')->where('id', $alreadyTimestampedUser->id)->value('settings'), true);
    $nonConsentingSettings = json_decode((string) DB::table('users')->where('id', $legacyNonConsentingUser->id)->value('settings'), true);

    expect($legacySettings['consent_to_training_at'])->toBe('2026-03-05T12:34:56+00:00');
    expect($alreadySettings['consent_to_training_at'])->toBe('2025-01-01T00:00:00+00:00');
    expect($nonConsentingSettings)->not->toHaveKey('consent_to_training_at');

    Carbon::setTestNow();
});

it('does nothing when no users require backfill', function (): void {
    User::factory()->create([
        'settings' => [
            'consent_to_training_at' => '2026-03-01T00:00:00+00:00',
        ],
    ]);

    $this->artisan('ml:backfill-consent-training-timestamps')
        ->expectsOutputToContain('No users require backfill.')
        ->assertExitCode(0);
});
