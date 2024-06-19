<?php

use App\Models\Group;
use App\Models\User;

test('a user can update a group', function (): void {
    $user = User::factory()->create();
    $group = Group::factory()->create(['name' => 'some name', 'user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson(route('groups.update', $group), [
        'name' => 'new name',
    ]);

    $response->assertOk();
    $response->assertJson([
        'group' => [
            'name' => 'new name',
        ],
    ]);

    expect($group->fresh()->name)->toBe('new name');
});

test('the group must belong to the user', function (): void {
    $user = User::factory()->create();
    $group = Group::factory()->create();

    $response = $this->actingAs($user)->postJson(route('groups.update', $group), [
        'name' => 'existing name',
    ]);

    $response->assertForbidden();

    expect($group->fresh()->name)->not()->toBe('existing name');
});

test('the group must be unique to the user', function (): void {
    $user = User::factory()->create();
    Group::factory()->create(['user_id' => $user->id, 'name' => 'existing name']);
    $group = Group::factory()->create(['user_id' => $user->id, 'name' => 'new name']);

    $response = $this->actingAs($user)->postJson(route('groups.update', $group), [
        'name' => 'existing name',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

test('the current group name is ignored when updating', function (): void {
    $user = User::factory()->create();
    $group = Group::factory()->create(['name' => 'some name', 'user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson(route('groups.update', $group), [
        'name' => 'some name',
    ]);

    $response->assertOk();
    $response->assertJson([
        'group' => [
            'name' => 'some name',
        ],
    ]);

    expect($group->fresh()->name)->toBe('some name');
});
