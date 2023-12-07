<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('a user can upload photos', function () {
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

test('a photo can not be larger than 20MB', function () {
    Storage::fake('public');
    $this->actingAs($user = User::factory()->create());

    $response = $this->post('/upload', [
        'photo' => UploadedFile::fake()->image('photo.jpg')->size(20481),
    ]);

    $response->assertSessionHasErrors('photo');

    expect($user->photos()->count())->toBeZero();
});

test('only images can be uploaded', function () {
    Storage::fake('public');
    $this->actingAs($user = User::factory()->create());

    $response = $this->post('/upload', [
        'photo' => UploadedFile::fake()->create('document.pdf'),
    ]);

    $response->assertSessionHasErrors('photo');

    expect($user->photos()->count())->toBeZero();
});
