<?php

use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

test('a photo has a full path', function () {
    Storage::fake(config('filesystems.default'));

    $photo = Photo::factory()->create([
        'path' => 'photos/photo.jpg',
    ]);

    expect($photo->full_path)->toBe(Storage::url('photos/photo.jpg'));
});
