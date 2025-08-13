<?php

use App\Actions\Photos\ExtractExifFromPhotoAction;
use App\Actions\Photos\ExtractsExifFromPhoto;
use App\DTO\UserSettings;
use App\Jobs\SuggestPhotoItem;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\Doubles\FakeExtractExifFromPhotoAction;

use function Pest\Laravel\freezeTime;

beforeEach(function (): void {
    Storage::fake(config('filesystems.default'));
    Bus::fake();
    freezeTime();
});

test('a user can upload photos', function (): void {
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
        ->and($photo->longitude)->toBe(-77.15449870066);
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

test('a user can upload a photo with the same name twice if different date taken', function (): void {
    $user = User::factory()->create();
    Photo::factory()->for($user)->create([
        'original_file_name' => 'photo.jpg',
        'taken_at_local' => now()->subDay()->toDateTimeString(),
    ]);

    $response = $this->actingAs($user)->post('/upload', [
        'photo' => UploadedFile::fake()->image('photo.jpg'),
    ]);

    $response->assertOk();

    expect($user->photos()->count())->toBe(2);
});

test('a user can not upload the same photo twice', function (): void {
    $this->swap(
        ExtractsExifFromPhoto::class,
        app(FakeExtractExifFromPhotoAction::class)->withExif([]),
    );
    $user = User::factory()->create();
    Photo::factory()->for($user)->create([
        'original_file_name' => 'photo.jpg',
        'taken_at_local' => null,
    ]);

    $response = $this->actingAs($user)->post('/upload', [
        'photo' => UploadedFile::fake()->image('photo.jpg'),
    ]);

    $response->assertSessionHasErrors('photo');

    expect($user->photos()->count())->toBe(1);
});

test('a user can not upload the same photo with same date taken twice', function (): void {
    $user = User::factory()->create();
    Photo::factory()->for($user)->create([
        'original_file_name' => 'photo.jpg',
        'taken_at_local' => now()->toDateTimeString(),
    ]);

    $response = $this->actingAs($user)->post('/upload', [
        'photo' => UploadedFile::fake()->image('photo.jpg'),
    ]);

    $response->assertSessionHasErrors('photo');

    expect($user->photos()->count())->toBe(1);
});

test('a user can upload a photo with the same name as another users photo', function (): void {
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
