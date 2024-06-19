<?php

use App\Models\Group;
use App\Models\User;

test('a user can delete a group', function (): void {
    $user = User::factory()->create();
    $group = Group::factory()->create(['name' => 'some name', 'user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson(route('groups.destroy', $group));

    $response->assertOk();

    expect($group->fresh())->toBeNull();
});

test('the group must belong to the user', function (): void {
    $user = User::factory()->create();
    $group = Group::factory()->create();

    $response = $this->actingAs($user)->deleteJson(route('groups.destroy', $group));

    $response->assertNotFound();

    expect($group->fresh()->name)->not->toBeNull();
});
