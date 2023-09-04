<?php

use App\Models\Photo;

test('a photo has a full path', function () {
    $photo = Photo::factory()->create([
        'path' => 'photos/photo.jpg',
    ]);

    expect($photo->full_path)->toBe('/storage/photos/photo.jpg');
});
