<?php

use App\Actions\Photos\GetItemFromPredictionAction;
use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('local');
    Storage::fake('public');
    Storage::fake('s3');
    Storage::disk('local')->makeDirectory('zips');
});

it('test it selects photos proportionally and limits users', function (): void {
    // Create 15 users, all consenting to training
    $users = User::factory(15)->create([
        'settings' => ['consent_to_training' => true],
    ]);

    $itemSlug = 'bottle';
    $itemName = GetItemFromPredictionAction::ITEM_CLASS_NAMES[$itemSlug];
    $item = Item::factory()->create(['name' => $itemName]);

    // Total 1000 photos across 15 users
    // User 0 (A): 400 photos
    // User 1 (B): 300 photos
    // Others (13 users): 300 photos total -> 23 each (approx)
    createPhotosForUserReal($users[0], $item, 400);
    createPhotosForUserReal($users[1], $item, 300);

    for ($i = 2; $i < 15; $i++) {
        createPhotosForUserReal($users[$i], $item, 23);
    }

    // Total = 400 + 300 + 13*23 = 700 + 299 = 999 photos

    // Run command with limit 100
    $this->artisan('app:select-photos-for-training', ['--limit' => 100])
        ->expectsOutputToContain('Bottle')
        ->expectsOutputToContain('100')
        ->assertExitCode(0);
});

it('test it only selects photos from consenting users', function (): void {
    $consentingUser = User::factory()->create(['settings' => ['consent_to_training' => true]]);
    $nonConsentingUser = User::factory()->create(['settings' => ['consent_to_training' => false]]);

    $item = Item::factory()->create(['name' => 'Bottle']);
    createPhotosForUserReal($consentingUser, $item, 10);
    createPhotosForUserReal($nonConsentingUser, $item, 10);

    $this->artisan('app:select-photos-for-training', ['--limit' => 100])
        ->expectsOutputToContain('Bottle')
        ->expectsOutputToContain('10') // Only consenting user's photos
        ->assertExitCode(0);
});

it('test it handles items with no photos', function (): void {
    Item::factory()->create(['name' => 'Bottle']);

    $this->artisan('app:select-photos-for-training', ['--limit' => 100])
        ->expectsOutputToContain('Processing item: Bottle')
        ->expectsOutputToContain('Bottle')
        ->expectsOutputToContain('0')
        ->assertExitCode(0);
});

it('test it takes all photos if below limit', function (): void {
    $user = User::factory()->create(['settings' => ['consent_to_training' => true]]);
    $item = Item::factory()->create(['name' => 'Bottle']);
    createPhotosForUserReal($user, $item, 50);

    $this->artisan('app:select-photos-for-training', ['--limit' => 100])
        ->expectsOutputToContain('Bottle')
        ->expectsOutputToContain('50')
        ->assertExitCode(0);
});

it('test it handles rounding to zero due to small proportions', function (): void {
    $users = User::factory(10)->create(['settings' => ['consent_to_training' => true]]);
    $item = Item::factory()->create(['name' => 'Bottle']);

    // 10 users with 10 photos each = 100 total.
    // Limit = 5. Ratio = 5/100 = 0.05.
    // Each user contribution: floor(10 * 0.05) = floor(0.5) = 0.
    foreach ($users as $user) {
        createPhotosForUserReal($user, $item, 10);
    }

    $this->artisan('app:select-photos-for-training', ['--limit' => 5])
        ->expectsOutputToContain('Bottle')
        ->expectsOutputToContain('5') // Should be 5, not 0
        ->assertExitCode(0);
});

it('test it processes multiple items', function (): void {
    $user = User::factory()->create(['settings' => ['consent_to_training' => true]]);
    $item1 = Item::factory()->create(['name' => 'Bottle']);
    $item2 = Item::factory()->create(['name' => 'Can']);

    createPhotosForUserReal($user, $item1, 7);
    createPhotosForUserReal($user, $item2, 9);

    $this->artisan('app:select-photos-for-training', ['--limit' => 100])
        ->expectsTable(['Item', 'Photos', 'Users'], [
            ['Bottle', '7', '1'],
            ['Can', '9', '1'],
        ])
        ->assertExitCode(0);
});

it('test it skips missing items', function (): void {
    // Don't create any items in DB

    $this->artisan('app:select-photos-for-training', ['--limit' => 100])
        ->expectsOutputToContain('Item not found: Aluminium Foil')
        ->assertExitCode(0);
});

it('test it fails to reach limit when many users exist due to ratio calculation', function (): void {
    $users = User::factory(20)->create(['settings' => ['consent_to_training' => true]]);
    $item = Item::factory()->create(['name' => 'Bottle']);

    // 20 users with 10 photos each = 200 total.
    // Requested limit = 100.
    // Ratio = 100 / 200 = 0.5.
    // We only take from top 10 users.
    // Each of top 10 users gives: floor(10 * 0.5) = 5 photos.
    // Total selected = 10 * 5 = 50.
    foreach ($users as $user) {
        createPhotosForUserReal($user, $item, 10);
    }

    $this->artisan('app:select-photos-for-training', ['--limit' => 100])
        ->expectsOutputToContain('Bottle')
        ->expectsOutputToContain('100') // Should reach the limit
        ->assertExitCode(0);
});

function createPhotosForUserReal(User $user, Item $item, int $count): void
{
    for ($i = 0; $i < $count; $i++) {
        $photo = new Photo;
        $photo->user_id = $user->id;
        $photo->size_kb = 100;
        $photo->path = "photos/{$user->id}_{$i}.jpg";
        $photo->original_file_name = "file_{$user->id}_{$i}.jpg";
        $photo->save();

        Storage::disk('public')->put($photo->path, 'dummy');

        $photoItem = new PhotoItem;
        $photoItem->photo_id = $photo->id;
        $photoItem->item_id = $item->id;
        $photoItem->save();
    }
}
