<?php

use App\Models\Photo;
use App\Models\User;
use App\Policies\PhotoPolicy;

test('owner can manage their own photo', function (): void {
    $user = User::factory()->make(['id' => 1]);
    $photo = Photo::factory()->make(['user_id' => 1]);

    $policy = new PhotoPolicy;

    expect($policy->manage($user, $photo)->allowed())->toBeTrue();
});

test('non-owner cannot manage another users photo', function (): void {
    $user = User::factory()->make(['id' => 1]);
    $photo = Photo::factory()->make(['user_id' => 2]);

    $policy = new PhotoPolicy;

    expect($policy->manage($user, $photo)->allowed())->toBeFalse();
});

test('admin can manage any photo', function (): void {
    $user = User::factory()->make(['id' => 1, 'email' => 'admin@test.com']);
    $photo = Photo::factory()->make(['user_id' => 2]);

    config(['app.admin_emails' => ['admin@test.com']]);

    $policy = new PhotoPolicy;

    expect($policy->manage($user, $photo)->allowed())->toBeTrue();
});
