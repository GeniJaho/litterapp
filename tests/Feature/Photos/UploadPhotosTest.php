<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('a user can upload photos', function () {
    Storage::fake('public');
    $this->actingAs($user = User::factory()->create());

    $response = $this->post('/upload', [
        'photos' => [
            UploadedFile::fake()->image('photo.jpg'),
        ],
    ]);

    $response->assertOk();

    expect($user->photos()->count())->toBeOne();

    $photo = $user->photos()->first();
    expect($photo->path)->toBe('photos/photo.jpg');

    Storage::disk('public')->assertExists('photos/photo.jpg');
});

test('a photo can not be larger than 2MB', function () {
    Storage::fake('public');
    $this->actingAs($user = User::factory()->create());

    $response = $this->post('/upload', [
        'photos' => [
            UploadedFile::fake()->image('photo.jpg')->size(2049),
        ],
    ]);

    $response->assertSessionHasErrors('photos.0');

    expect($user->photos()->count())->toBeZero();
});

test('only images can be uploaded', function () {
    Storage::fake('public');
    $this->actingAs($user = User::factory()->create());

    $response = $this->post('/upload', [
        'photos' => [
            UploadedFile::fake()->create('document.pdf'),
        ],
    ]);

    $response->assertSessionHasErrors('photos.0');

    expect($user->photos()->count())->toBeZero();
});
