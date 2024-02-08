<?php

use App\Actions\Photos\ExtractLocationFromPhotoAction;
use App\Actions\Photos\ExtractsLocationFromPhoto;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake(config('filesystems.default'));
});

test('a user can upload photos', function () {
    $this->actingAs($user = User::factory()->create());
    $file = UploadedFile::fake()->image('photo.jpg');

    $response = $this->post('/upload', [
        'photo' => $file,
    ]);

    $response->assertOk();

    expect($user->photos()->count())->toBe(1);

    $photo = $user->photos()->first();
    expect($photo->path)->toBe('photos/'.$file->hashName());

    Storage::assertExists('photos/'.$file->hashName());
});

test('a user can upload photos with location data', function () {
    $this->swap(ExtractsLocationFromPhoto::class, new ExtractLocationFromPhotoAction());
    $this->actingAs($user = User::factory()->create());

    $file = UploadedFile::fake()->createWithContent(
        'photo.jpg',
        file_get_contents(storage_path('app/photo-with-gps.jpg')),
    );

    $response = $this->post('/upload', ['photo' => $file]);

    $response->assertOk();

    expect($user->photos()->count())->toBe(1);

    $photo = $user->photos()->first();
    expect($photo->latitude)->toBe(40.053030045789)
        ->and($photo->longitude)->toBe(-77.15449870066);
})->group('slow');

test('a photo can not be larger than 20MB', function () {
    $this->actingAs($user = User::factory()->create());
    $response = $this->post('/upload', [
        'photo' => UploadedFile::fake()->image('photo.jpg')->size(20481),
    ]);

    $response->assertSessionHasErrors('photo');

    expect($user->photos()->count())->toBeZero();
});

test('only images can be uploaded', function () {
    $this->actingAs($user = User::factory()->create());
    $response = $this->post('/upload', [
        'photo' => UploadedFile::fake()->create('document.pdf'),
    ]);

    $response->assertSessionHasErrors('photo');

    expect($user->photos()->count())->toBeZero();
});

test('a user can not upload the same photo twice', function () {
    $user = User::factory()->create();
    Photo::factory()->for($user)->create([
        'original_file_name' => 'photo.jpg',
    ]);

    $response = $this->actingAs($user)->post('/upload', [
        'photo' => UploadedFile::fake()->image('photo.jpg'),
    ]);

    $response->assertSessionHasErrors('photo');

    expect($user->photos()->count())->toBe(1);
});

test('a user can upload a photo with the same name as another users photo', function () {
    Photo::factory()->create([
        'original_file_name' => 'photo.jpg',
    ]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/upload', [
        'photo' => UploadedFile::fake()->image('photo.jpg'),
    ]);

    $response->assertOk();

    expect($user->photos()->count())->toBe(1);
});
