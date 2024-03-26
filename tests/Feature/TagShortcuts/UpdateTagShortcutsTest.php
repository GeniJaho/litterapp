<?php

use App\Models\TagShortcut;
use App\Models\User;

test('a user can update a tag shortcut', function (): void {
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

test('the shortcut must belong to the user', function (): void {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->create();

    $response = $this->actingAs($user)->postJson(route('tag-shortcuts.update', $tagShortcut), [
        'shortcut' => 'existing shortcut',
    ]);

    $response->assertForbidden();

    expect($tagShortcut->fresh()->shortcut)->not()->toBe('existing shortcut');
});

test('the shortcut must be unique to the user', function (): void {
    $user = User::factory()->create();
    TagShortcut::factory()->create(['user_id' => $user->id, 'shortcut' => 'existing shortcut']);
    $tagShortcut = TagShortcut::factory()->create(['user_id' => $user->id, 'shortcut' => 'new name']);

    $response = $this->actingAs($user)->postJson(route('tag-shortcuts.update', $tagShortcut), [
        'shortcut' => 'existing shortcut',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('shortcut');
});

test('the current shortcut name is ignored when updating', function (): void {
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
