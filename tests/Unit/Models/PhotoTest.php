<?php

use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

test('a photo has a full path', function () {
    Storage::fake('s3');

    $photo = Photo::factory()->create([
        'path' => 'photos/photo.jpg',
    ]);

    expect($photo->full_path)->toBe(Storage::disk('s3')->url('photos/photo.jpg'));
});
