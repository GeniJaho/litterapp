<?php

use App\Models\Group;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

test('a photo has a full path', function (): void {
    Storage::fake(config('filesystems.default'));

    $photo = Photo::factory()->create([
        'path' => 'photos/photo.jpg',
    ]);

    expect($photo->full_path)->toBe(Storage::url('photos/photo.jpg'));
});

test('a photo belongs to many groups', function (): void {
    $photo = Photo::factory()->create();
    $group = Group::factory()->create();

    $photo->groups()->attach($group);

    expect($photo->groups)->toHaveCount(1)
        ->and($photo->groups->first()->id)->toBe($group->id);
});
