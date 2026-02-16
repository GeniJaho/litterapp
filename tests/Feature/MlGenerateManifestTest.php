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
    Storage::fake('s3');

    $this->brandType = TagType::factory()->create(['name' => 'Brand', 'slug' => 'brand']);
    $this->contentType = TagType::factory()->create(['name' => 'Content', 'slug' => 'content']);
});

it('generates a manifest csv and uploads to s3', function (): void {
    $user = User::factory()->create(['settings' => ['consent_to_training' => true]]);
    $item = Item::factory()->create(['name' => 'Can']);
    $brandTag = Tag::factory()->create(['tag_type_id' => $this->brandType->id, 'name' => 'Red Bull']);
    $contentTag = Tag::factory()->create(['tag_type_id' => $this->contentType->id, 'name' => 'Energy drink']);

    $photo = Photo::factory()->create(['user_id' => $user->id, 'path' => 'photos/2024/01/abc.jpg']);
    $photoItem = PhotoItem::create(['photo_id' => $photo->id, 'item_id' => $item->id]);
    PhotoItemTag::create(['photo_item_id' => $photoItem->id, 'tag_id' => $brandTag->id]);
    PhotoItemTag::create(['photo_item_id' => $photoItem->id, 'tag_id' => $contentTag->id]);

    $this->artisan('ml:generate-manifest')
        ->expectsOutputToContain('Manifest generation complete')
        ->assertExitCode(0);

    $files = Storage::disk('s3')->allFiles('ml/manifests');
    expect($files)->toHaveCount(1);

    $csv = Storage::disk('s3')->get($files[0]);
    $lines = array_filter(explode("\n", trim((string) $csv)));
    expect($lines)->toHaveCount(2); // header + 1 row

    $header = str_getcsv($lines[0]);
    expect($header)->toBe(['photo_id', 's3_key', 'item_id', 'brand_tag_ids', 'content_tag_ids']);

    $row = str_getcsv($lines[1]);
    expect($row[0])->toBe((string) $photo->id);
    expect($row[1])->toBe('photos/2024/01/abc.jpg');
    expect($row[2])->toBe((string) $item->id);
    expect($row[3])->toBe((string) $brandTag->id);
    expect($row[4])->toBe((string) $contentTag->id);
});

it('excludes photos from non-consenting users', function (): void {
    $consentingUser = User::factory()->create(['settings' => ['consent_to_training' => true]]);
    $nonConsentingUser = User::factory()->create(['settings' => ['consent_to_training' => false]]);
    $item = Item::factory()->create(['name' => 'Bottle']);

    Photo::factory()->create(['user_id' => $consentingUser->id]);
    $consentingPhoto = Photo::factory()->create(['user_id' => $consentingUser->id]);
    PhotoItem::create(['photo_id' => $consentingPhoto->id, 'item_id' => $item->id]);

    $nonConsentingPhoto = Photo::factory()->create(['user_id' => $nonConsentingUser->id]);
    PhotoItem::create(['photo_id' => $nonConsentingPhoto->id, 'item_id' => $item->id]);

    $this->artisan('ml:generate-manifest')->assertExitCode(0);

    $files = Storage::disk('s3')->allFiles('ml/manifests');
    $csv = Storage::disk('s3')->get($files[0]);
    $lines = array_filter(explode("\n", trim((string) $csv)));

    expect($lines)->toHaveCount(2); // header + 1 consenting photo
});

it('excludes non-visual items', function (): void {
    $user = User::factory()->create(['settings' => ['consent_to_training' => true]]);
    $goodItem = Item::factory()->create(['name' => 'Can']);
    $excludedItem = Item::factory()->create(['name' => 'Piece of <add material>']);

    $goodPhoto = Photo::factory()->create(['user_id' => $user->id]);
    PhotoItem::create(['photo_id' => $goodPhoto->id, 'item_id' => $goodItem->id]);

    $excludedPhoto = Photo::factory()->create(['user_id' => $user->id]);
    PhotoItem::create(['photo_id' => $excludedPhoto->id, 'item_id' => $excludedItem->id]);

    $this->artisan('ml:generate-manifest')->assertExitCode(0);

    $files = Storage::disk('s3')->allFiles('ml/manifests');
    $csv = Storage::disk('s3')->get($files[0]);
    $lines = array_filter(explode("\n", trim((string) $csv)));

    expect($lines)->toHaveCount(2); // header + 1 good photo only
});

it('excludes placeholder brand and content tags from output', function (): void {
    $user = User::factory()->create(['settings' => ['consent_to_training' => true]]);
    $item = Item::factory()->create(['name' => 'Bottle']);
    $goodBrand = Tag::factory()->create(['tag_type_id' => $this->brandType->id, 'name' => 'Heineken']);
    $excludedBrand = Tag::factory()->create(['tag_type_id' => $this->brandType->id, 'name' => 'OTHER (Please add this missing brand to the picklist)']);

    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $photoItem = PhotoItem::create(['photo_id' => $photo->id, 'item_id' => $item->id]);
    PhotoItemTag::create(['photo_item_id' => $photoItem->id, 'tag_id' => $goodBrand->id]);
    PhotoItemTag::create(['photo_item_id' => $photoItem->id, 'tag_id' => $excludedBrand->id]);

    $this->artisan('ml:generate-manifest')->assertExitCode(0);

    $files = Storage::disk('s3')->allFiles('ml/manifests');
    $csv = Storage::disk('s3')->get($files[0]);
    $lines = array_filter(explode("\n", trim((string) $csv)));
    $row = str_getcsv($lines[1]);

    expect($row[3])->toBe((string) $goodBrand->id); // Only good brand, not excluded
});

it('supports --since flag for delta manifests', function (): void {
    $user = User::factory()->create(['settings' => ['consent_to_training' => true]]);
    $item = Item::factory()->create(['name' => 'Can']);

    $oldPhoto = Photo::factory()->create(['user_id' => $user->id]);
    PhotoItem::create(['photo_id' => $oldPhoto->id, 'item_id' => $item->id, 'created_at' => '2025-01-01']);

    $newPhoto = Photo::factory()->create(['user_id' => $user->id]);
    PhotoItem::create(['photo_id' => $newPhoto->id, 'item_id' => $item->id, 'created_at' => '2026-02-10']);

    $this->artisan('ml:generate-manifest', ['--since' => '2026-02-01'])->assertExitCode(0);

    $files = Storage::disk('s3')->allFiles('ml/manifests');
    $csv = Storage::disk('s3')->get($files[0]);
    $lines = array_filter(explode("\n", trim((string) $csv)));

    expect($lines)->toHaveCount(2); // header + 1 new photo only
    expect($files[0])->toContain('manifest_delta_');
});

it('handles multiple brand and content tags with pipe separator', function (): void {
    $user = User::factory()->create(['settings' => ['consent_to_training' => true]]);
    $item = Item::factory()->create(['name' => 'Can']);
    $brand1 = Tag::factory()->create(['tag_type_id' => $this->brandType->id, 'name' => 'Red Bull']);
    $brand2 = Tag::factory()->create(['tag_type_id' => $this->brandType->id, 'name' => 'Monster']);
    $content1 = Tag::factory()->create(['tag_type_id' => $this->contentType->id, 'name' => 'Energy drink']);

    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $photoItem = PhotoItem::create(['photo_id' => $photo->id, 'item_id' => $item->id]);
    PhotoItemTag::create(['photo_item_id' => $photoItem->id, 'tag_id' => $brand1->id]);
    PhotoItemTag::create(['photo_item_id' => $photoItem->id, 'tag_id' => $brand2->id]);
    PhotoItemTag::create(['photo_item_id' => $photoItem->id, 'tag_id' => $content1->id]);

    $this->artisan('ml:generate-manifest')->assertExitCode(0);

    $files = Storage::disk('s3')->allFiles('ml/manifests');
    $csv = Storage::disk('s3')->get($files[0]);
    $lines = array_filter(explode("\n", trim((string) $csv)));
    $row = str_getcsv($lines[1]);

    $brandIds = explode('|', (string) $row[3]);
    expect($brandIds)->toHaveCount(2);
    expect($brandIds)->toContain((string) $brand1->id, (string) $brand2->id);
    expect($row[4])->toBe((string) $content1->id);
});

it('fails when no users have consented', function (): void {
    User::factory()->create(['settings' => ['consent_to_training' => false]]);

    $this->artisan('ml:generate-manifest')
        ->expectsOutputToContain('No users have consented')
        ->assertExitCode(1);
});
