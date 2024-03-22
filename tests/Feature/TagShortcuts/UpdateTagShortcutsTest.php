<?php

use App\Models\TagShortcut;
use App\Models\User;

test('a user can update a tag shortcut', function () {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->create(['shortcut' => 'some shortcut', 'user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson(route('tag-shortcuts.update', $tagShortcut), [
        'shortcut' => 'new name',
    ]);

    $response->assertOk();
    $response->assertJson([
        'tagShortcut' => [
            'shortcut' => 'new name',
        ],
    ]);

    expect($tagShortcut->fresh()->shortcut)->toBe('new name');
});

test('the shortcut must be unique to the user', function () {
    $user = User::factory()->create();
    TagShortcut::factory()->create(['user_id' => $user->id, 'shortcut' => 'existing shortcut']);
    $tagShortcut = TagShortcut::factory()->create(['user_id' => $user->id, 'shortcut' => 'new name']);

    $response = $this->actingAs($user)->postJson(route('tag-shortcuts.update', $tagShortcut), [
        'shortcut' => 'existing shortcut',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('shortcut');
});

test('the current shortcut name is ignored when updating', function () {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->create(['shortcut' => 'some shortcut', 'user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson(route('tag-shortcuts.update', $tagShortcut), [
        'shortcut' => 'some shortcut',
    ]);

    $response->assertOk();
    $response->assertJson([
        'tagShortcut' => [
            'shortcut' => 'some shortcut',
        ],
    ]);

    expect($tagShortcut->fresh()->shortcut)->toBe('some shortcut');
});
