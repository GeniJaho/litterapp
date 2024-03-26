<?php

use App\Models\TagShortcut;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

test('a user has many tag shortcuts', function (): void {
    $user = User::factory()->create();
    $tagShortcuts = TagShortcut::factory(2)->create(['user_id' => $user->id]);

    $user->refresh();

    expect($user->tagShortcuts)->toBeInstanceOf(Collection::class)
        ->toHaveCount(2)
        ->and($user->tagShortcuts->pluck('id'))
        ->toEqualCanonicalizing($tagShortcuts->pluck('id'));
});
