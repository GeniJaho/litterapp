<?php

use App\Jobs\MinifyProfilePhoto;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;

test('it minifies the profile photo', function () {
    Storage::fake('public');
    Storage::disk('public')->put(
        'profile-photos/default.jpg',
        Storage::disk('local')->get('default.jpg')
    );
    $user = User::factory()->create([
        'profile_photo_path' => 'profile-photos/default.jpg',
    ]);
    $previousSize = Storage::disk('public')->size($user->profile_photo_path);

    $action = new MinifyProfilePhoto($user);
    $action->handle(new Driver());

    $this->assertFileExists(
        Storage::disk('public')->path($user->profile_photo_path)
    );
    $this->assertLessThan(
        $previousSize,
        Storage::disk('public')->size($user->profile_photo_path)
    );
})->group('slow');
