<?php

use App\Jobs\MinifyProfilePhoto;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

test('profile information can be updated', function () {
    Queue::fake();
    Storage::fake('public');
    $this->actingAs($user = User::factory()->create());

    $response = $this->put('/user/profile-information', [
        'name' => 'Test Name',
        'email' => 'test@example.com',
        'photo' => UploadedFile::fake()->image('photo.png'),
    ]);

    $response->assertRedirect();
    expect($user->fresh())
        ->name->toEqual('Test Name')
        ->email->toEqual('test@example.com')
        ->profile_photo_path->not()->toBeNull();

    Storage::disk('public')->assertExists($user->fresh()->profile_photo_path);
    Queue::assertPushed(MinifyProfilePhoto::class);
});
