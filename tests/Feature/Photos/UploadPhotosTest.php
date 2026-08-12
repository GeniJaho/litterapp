<?php

use App\Actions\Photos\ExtractExifFromPhotoAction;
use App\Actions\Photos\ExtractsExifFromPhoto;
use App\DTO\UserSettings;
use App\Jobs\SuggestPhotoItem;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\freezeTime;

beforeEach(function (): void {
    Storage::fake(config('filesystems.default'));
    Bus::fake();
    freezeTime();
});

test('a user can upload photos', function (): void {
    $this->actingAs($user = User::factory()->create());
    $file = UploadedFile::fake()->image('photo.jpg')->size(10);
    $expectedHash = hash_file('sha256', $file->getRealPath());

    $response = $this->post('/upload', [
        'photo' => $file,
    ]);

    $response->assertOk();

    expect($user->photos()->count())->toBe(1);

    $photo = $user->photos()->first();
    expect($photo->path)->toBe('photos/'.$file->hashName())
        ->and($photo->size_kb)->toBe(10)
        ->and($photo->details->content_hash)->toBe($expectedHash);

    Storage::assertExists('photos/'.$file->hashName());
});

test('photos larger than 300kb are minified', function (): void {
    $this->actingAs($user = User::factory()->create());

    $file = UploadedFile::fake()->image('photo.jpg', 1200, 1200)->size(301);

    $response = $this->post('/upload', [
        'photo' => $file,
    ]);

    $response->assertOk();

    $photo = $user->photos()->first();

    expect($photo->size_kb)->toBeLessThan(301);

    Storage::assertExists('photos/'.$file->hashName());
});

test('a user can upload photos with location data', function (): void {
    $this->swap(ExtractsExifFromPhoto::class, app(ExtractExifFromPhotoAction::class));
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
        ->and($photo->longitude)->toBe(-77.15449870066)
        ->and($photo->size_kb)->toBe(1);
})->group('slow');

test('a user can upload photos with the date the photo is taken', function (): void {
    Storage::fake('public');
    $this->swap(ExtractsExifFromPhoto::class, app(ExtractExifFromPhotoAction::class));
    $this->actingAs($user = User::factory()->create());

    $file = UploadedFile::fake()->createWithContent(
        'photo.jpg',
        file_get_contents(storage_path('app/photo-with-gps.jpg')),
    );

    $response = $this->post('/upload', ['photo' => $file]);

    $response->assertOk();

    expect($user->photos()->count())->toBe(1);

    $photo = $user->photos()->first();
    expect($photo->taken_at_local)->toBe('2019-10-10 12:00:00');
})->group('slow')->skip('Properly implement this');

test('a photo can not be larger than 20MB', function (): void {
    $this->actingAs($user = User::factory()->create());
    $response = $this->post('/upload', [
        'photo' => UploadedFile::fake()->image('photo.jpg')->size(20481),
    ]);

    $response->assertSessionHasErrors('photo');

    expect($user->photos()->count())->toBeZero();
});

test('only images can be uploaded', function (): void {
    $this->actingAs($user = User::factory()->create());
    $response = $this->post('/upload', [
        'photo' => UploadedFile::fake()->create('document.pdf'),
    ]);

    $response->assertSessionHasErrors('photo');

    expect($user->photos()->count())->toBeZero();
});

test('a user can upload two photos with the same name but different content', function (): void {
    $this->actingAs($user = User::factory()->create());

    $this->post('/upload', ['photo' => UploadedFile::fake()->image('photo.jpg', 10, 10)])->assertOk();
    $this->post('/upload', ['photo' => UploadedFile::fake()->image('photo.jpg', 20, 20)])->assertOk();

    expect($user->photos()->count())->toBe(2);
});

test('a user can not upload the same photo twice', function (): void {
    $this->actingAs($user = User::factory()->create());

    $this->post('/upload', ['photo' => UploadedFile::fake()->image('photo.jpg')])->assertOk();
    $this->post('/upload', ['photo' => UploadedFile::fake()->image('photo.jpg')])
        ->assertSessionHasErrors('photo');

    expect($user->photos()->count())->toBe(1);
});

test('a user can not upload the same photo even with a different name', function (): void {
    $this->actingAs($user = User::factory()->create());

    $this->post('/upload', ['photo' => UploadedFile::fake()->image('first.jpg')])->assertOk();
    $this->post('/upload', ['photo' => UploadedFile::fake()->image('second.jpg')])
        ->assertSessionHasErrors('photo');

    expect($user->photos()->count())->toBe(1);
});

test('a user can upload the same photo as another user', function (): void {
    $this->actingAs($otherUser = User::factory()->create())
        ->post('/upload', ['photo' => UploadedFile::fake()->image('photo.jpg')])
        ->assertOk();

    $this->actingAs($user = User::factory()->create())
        ->post('/upload', ['photo' => UploadedFile::fake()->image('photo.jpg')])
        ->assertOk();

    expect($otherUser->photos()->count())->toBe(1)
        ->and($user->photos()->count())->toBe(1);
});

test('the SuggestPhotoItem job is dispatched when all conditions are met', function (): void {
    $user = User::factory()->create(['settings' => new UserSettings(litterbot_enabled: true)]);
    $file = UploadedFile::fake()->image('photo.jpg');

    $this->actingAs($user)->post('/upload', ['photo' => $file])->assertOk();

    $photo = $user->photos()->first();

    Bus::assertDispatched(fn (SuggestPhotoItem $job): bool => $job->photo->id === $photo->id);
});

test('the SuggestPhotoItem job is not dispatched when litterBotEnabled is false', function (): void {
    $user = User::factory()->create(['settings' => new UserSettings(litterbot_enabled: false)]);
    $file = UploadedFile::fake()->image('photo.jpg');

    $this->actingAs($user)->post('/upload', ['photo' => $file])->assertOk();

    Bus::assertNotDispatched(SuggestPhotoItem::class);
});

test('the SuggestPhotoItem job is not dispatched when LitterBot is disabled app-wide', function (): void {
    AppSetting::query()->create(['key' => 'litterbot_enabled', 'value' => '0']);

    $user = User::factory()->create(['settings' => new UserSettings(litterbot_enabled: true)]);
    $file = UploadedFile::fake()->image('photo.jpg');

    $this->actingAs($user)->post('/upload', ['photo' => $file])->assertOk();

    expect($user->photos()->count())->toBe(1);

    Bus::assertNotDispatched(SuggestPhotoItem::class);
});
