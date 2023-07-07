<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('a user can upload a photo', function () {
    Storage::fake('public');
    $this->actingAs($user = User::factory()->create());

    $response = $this->post('/upload', [
        'photo' => UploadedFile::fake()->image('photo.jpg'),
    ]);

    $response->assertOk();
    expect($user->photos()->count())->toBeOne();

    $photo = $user->photos()->first();
    expect($photo->path)->toBe('photos/photo.jpg');

    Storage::disk('public')->assertExists('photos/photo.jpg');
});
