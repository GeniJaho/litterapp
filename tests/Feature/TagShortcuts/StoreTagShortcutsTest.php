<?php

use App\Models\TagShortcut;
use App\Models\User;

test('a user can store a tag shortcut', function (): void {
    $user = User::factory()->create();
    TagShortcut::factory()->create(['shortcut' => 'some shortcut']);

    $response = $this->actingAs($user)->post(route('tag-shortcuts.store'), [
        'shortcut' => 'some shortcut',
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('tag_shortcuts', [
        'user_id' => $user->id,
        'shortcut' => 'some shortcut',
    ]);
});

test('the shortcut must be unique to the user', function (): void {
    $user = User::factory()->create();
    TagShortcut::factory()->create(['user_id' => $user->id, 'shortcut' => 'some shortcut']);

    $response = $this->actingAs($user)->postJson(route('tag-shortcuts.store'), [
        'shortcut' => 'some shortcut',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('shortcut');
});
