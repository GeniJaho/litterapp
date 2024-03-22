<?php

use App\Models\TagShortcut;
use App\Models\User;

test('a user can delete a tag shortcut', function () {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->create(['shortcut' => 'some shortcut', 'user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson(route('tag-shortcuts.destroy', $tagShortcut));

    $response->assertOk();

    expect($tagShortcut->fresh())->toBeNull();
});

test('the shortcut must belong to the user', function () {
    $user = User::factory()->create();
    $tagShortcut = TagShortcut::factory()->create();

    $response = $this->actingAs($user)->deleteJson(route('tag-shortcuts.update', $tagShortcut));

    $response->assertNotFound();

    expect($tagShortcut->fresh()->shortcut)->not->toBeNull();
});
