<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

test('a user can list their groups', function (): void {
    $user = User::factory()->create();
    $group = Group::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('groups.index'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Groups/Index')
        ->has('groups', 1)
        ->where('groups.0.id', $group->id)
        ->where('groups.0.name', $group->name)
        ->etc()
    );
});
