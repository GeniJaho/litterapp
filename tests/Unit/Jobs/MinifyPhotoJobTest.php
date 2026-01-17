<?php

use App\Jobs\MinifyPhoto;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

test('it minifies the photo and updates size_kb', function (): void {
    Storage::fake(config('filesystems.default'));
    Storage::put(
        'photos/test.jpg',
        file_get_contents(storage_path('app/default.jpg'))
    );
    $photo = Photo::factory()->create([
        'path' => 'photos/test.jpg',
        'size_kb' => null,
    ]);
    $previousSize = Storage::size($photo->path);

    $job = new MinifyPhoto($photo);
    $job->handle();

    Storage::assertExists($photo->path);
    $newSize = Storage::size($photo->path);
    expect($newSize)->toBeLessThan($previousSize);

    $photo->refresh();
    expect($photo->size_kb)->toBe((int) round($newSize / 1024));
})->group('slow');
