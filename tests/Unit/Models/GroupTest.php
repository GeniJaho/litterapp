<?php

use App\Models\Group;
use App\Models\Photo;
use App\Models\User;

test('a group belongs to a user', function (): void {
    $user = User::factory()->create();
    $group = Group::factory()->create(['user_id' => $user->id]);

    expect($group->user->id)->toBe($user->id);
});

test('a group belongs to many photos', function (): void {
    $group = Group::factory()->create();
    $photo = Photo::factory()->create();

    $group->photos()->attach($photo);

    expect($group->photos)->toHaveCount(1)
        ->and($group->photos->first()->id)->toBe($photo->id);
});
