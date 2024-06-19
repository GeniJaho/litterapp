<?php

use App\Models\Group;
use App\Models\User;

test('a user can store a group', function (): void {
    $this->freezeTime();
    $user = User::factory()->create();
    Group::factory()->create(['name' => 'some name']);

    $response = $this->actingAs($user)->post(route('groups.store'), [
        'name' => 'some name',
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('groups', [
        'user_id' => $user->id,
        'name' => 'some name',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});

test('the group must be unique to the user', function (): void {
    $user = User::factory()->create();
    Group::factory()->create(['user_id' => $user->id, 'name' => 'some name']);

    $response = $this->actingAs($user)->postJson(route('groups.store'), [
        'name' => 'some name',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('name');
});

test('the request is validated', function ($data, $error): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('groups.store'), $data);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors($error);
})->with([
    'empty name' => [['name' => ''], 'name'],
    'long name' => [['name' => str_repeat('a', 256)], 'name'],
]);
