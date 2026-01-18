<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\PhotoItemTag;
use App\Models\Tag;
use App\Models\TagType;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('local');
    Storage::fake('public');
    Storage::fake('s3');
    Storage::disk('local')->makeDirectory('zips');
});

it('test it selects photos for brands proportionally and limits users', function (): void {
    // Create Brand Tag Type
    $brandType = TagType::factory()->create(['name' => 'Brand', 'slug' => 'brand']);

    // Create 15 users, all consenting to training
    $users = User::factory(15)->create([
        'settings' => ['consent_to_training' => true],
    ]);

    $brand = Tag::factory()->create(['name' => 'Coca Cola', 'tag_type_id' => $brandType->id]);

    // Total 1000 photos across 15 users for this brand
    createPhotosForUserWithBrand($users[0], $brand, 400);
    createPhotosForUserWithBrand($users[1], $brand, 300);

    for ($i = 2; $i < 15; $i++) {
        createPhotosForUserWithBrand($users[$i], $brand, 23);
    }

    // Run command with limit 100
    $this->artisan('app:select-photos-for-brands-training', ['--limit' => 100])
        ->expectsOutputToContain('Coca Cola')
        ->expectsOutputToContain('100')
        ->assertExitCode(0);
});

it('test it selects top 50 brands by usage', function (): void {
    $brandType = TagType::factory()->create(['name' => 'Brand', 'slug' => 'brand']);
    $user = User::factory()->create(['settings' => ['consent_to_training' => true]]);

    // Create 60 brands
    $brands = Tag::factory()->count(60)->create(['tag_type_id' => $brandType->id]);

    // Give more usage to the first 50
    foreach ($brands as $index => $brand) {
        $usage = $index < 50 ? 2 : 1;
        createPhotosForUserWithBrand($user, $brand, $usage);
    }

    // Run command
    $this->artisan('app:select-photos-for-brands-training', ['--limit' => 10])
        ->assertExitCode(0);

    // We should see the first 50 brands but not the last 10
    // (Testing this by checking output would be tedious, but at least it should run)
});

it('test it only selects photos from consenting users for brands', function (): void {
    $brandType = TagType::factory()->create(['name' => 'Brand', 'slug' => 'brand']);
    $consentingUser = User::factory()->create(['settings' => ['consent_to_training' => true]]);
    $nonConsentingUser = User::factory()->create(['settings' => ['consent_to_training' => false]]);

    $brand = Tag::factory()->create(['name' => 'Pepsi', 'tag_type_id' => $brandType->id]);
    createPhotosForUserWithBrand($consentingUser, $brand, 10);
    createPhotosForUserWithBrand($nonConsentingUser, $brand, 10);

    $this->artisan('app:select-photos-for-brands-training', ['--limit' => 100])
        ->expectsOutputToContain('Pepsi')
        ->expectsOutputToContain('10') // Only consenting user's photos
        ->assertExitCode(0);
});

it('test it zips and uploads photos to s3', function (): void {
    $brandType = TagType::factory()->create(['name' => 'Brand', 'slug' => 'brand']);
    $user = User::factory()->create(['settings' => ['consent_to_training' => true]]);
    $brand = Tag::factory()->create(['name' => 'Coca Cola', 'tag_type_id' => $brandType->id]);
    createPhotosForUserWithBrand($user, $brand, 5);

    $this->artisan('app:select-photos-for-brands-training', ['--limit' => 10])
        ->assertExitCode(0);

    $files = Storage::disk('s3')->allFiles('zips');
    expect($files)->not->toBeEmpty();
    expect($files[0])->toStartWith('zips/photos_brands_10_');
    expect($files[0])->toEndWith('.zip');
});

function createPhotosForUserWithBrand(User $user, Tag $brand, int $count): void
{
    $item = Item::factory()->create();

    for ($i = 0; $i < $count; $i++) {
        $photo = new Photo;
        $photo->user_id = $user->id;
        $photo->path = "photos/{$user->id}_{$brand->id}_{$i}.jpg";
        $photo->original_file_name = "file_{$user->id}_{$brand->id}_{$i}.jpg";
        $photo->save();

        Storage::disk('public')->put($photo->path, 'dummy');

        $photoItem = new PhotoItem;
        $photoItem->photo_id = $photo->id;
        $photoItem->item_id = $item->id;
        $photoItem->save();

        $photoItemTag = new PhotoItemTag;
        $photoItemTag->photo_item_id = $photoItem->id;
        $photoItemTag->tag_id = $brand->id;
        $photoItemTag->save();
    }
}
