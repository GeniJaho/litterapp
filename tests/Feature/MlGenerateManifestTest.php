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
    $user = User::factory()->create(['settings' => ['consent_to_training_at' => now()->toIso8601String()]]);
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

    $csv = Storage::disk('s3')->get('ml/manifests/manifest_latest.csv');
    $lines = array_values(array_filter(explode("\n", trim((string) $csv))));
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

it('uploads both a dated snapshot and a manifest_latest pointer', function (): void {
    $user = User::factory()->create(['settings' => ['consent_to_training_at' => now()->toIso8601String()]]);
    $item = Item::factory()->create(['name' => 'Can']);
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    PhotoItem::create(['photo_id' => $photo->id, 'item_id' => $item->id]);

    $this->artisan('ml:generate-manifest')->assertExitCode(0);

    $datedKey = 'ml/manifests/manifest_'.now()->format('Y-m-d').'.csv';

    Storage::disk('s3')->assertExists($datedKey);
    Storage::disk('s3')->assertExists('ml/manifests/manifest_latest.csv');

    $dated = Storage::disk('s3')->get($datedKey);
    $latest = Storage::disk('s3')->get('ml/manifests/manifest_latest.csv');
    expect($latest)->toBe($dated);
});

it('emits one row per photo-item so a photo with multiple items appears multiple times', function (): void {
    $user = User::factory()->create(['settings' => ['consent_to_training_at' => now()->toIso8601String()]]);
    $itemA = Item::factory()->create(['name' => 'Can']);
    $itemB = Item::factory()->create(['name' => 'Bottle']);

    $photo = Photo::factory()->create(['user_id' => $user->id]);
    PhotoItem::create(['photo_id' => $photo->id, 'item_id' => $itemA->id]);
    PhotoItem::create(['photo_id' => $photo->id, 'item_id' => $itemB->id]);

    $this->artisan('ml:generate-manifest')->assertExitCode(0);

    $csv = Storage::disk('s3')->get('ml/manifests/manifest_latest.csv');
    $rows = array_values(array_slice(array_filter(explode("\n", trim((string) $csv))), 1));

    expect($rows)->toHaveCount(2); // both items for the same photo

    $photoIds = collect($rows)->map(fn (string $line): string => str_getcsv($line)[0])->unique()->values()->all();
    $itemIds = collect($rows)->map(fn (string $line): string => str_getcsv($line)[2])->all();

    expect($photoIds)->toBe([(string) $photo->id]);
    expect($itemIds)->toContain((string) $itemA->id, (string) $itemB->id);
});

it('excludes photos from non-consenting users', function (): void {
    $consentingUser = User::factory()->create(['settings' => ['consent_to_training_at' => now()->toIso8601String()]]);
    $nonConsentingUser = User::factory()->create(['settings' => ['consent_to_training_at' => null]]);
    $item = Item::factory()->create(['name' => 'Bottle']);

    Photo::factory()->create(['user_id' => $consentingUser->id]);
    $consentingPhoto = Photo::factory()->create(['user_id' => $consentingUser->id]);
    PhotoItem::create(['photo_id' => $consentingPhoto->id, 'item_id' => $item->id]);

    $nonConsentingPhoto = Photo::factory()->create(['user_id' => $nonConsentingUser->id]);
    PhotoItem::create(['photo_id' => $nonConsentingPhoto->id, 'item_id' => $item->id]);

    $this->artisan('ml:generate-manifest')->assertExitCode(0);

    $csv = Storage::disk('s3')->get('ml/manifests/manifest_latest.csv');
    $lines = array_values(array_filter(explode("\n", trim((string) $csv))));

    expect($lines)->toHaveCount(2); // header + 1 consenting photo
});

it('excludes non-visual items', function (): void {
    $user = User::factory()->create(['settings' => ['consent_to_training_at' => now()->toIso8601String()]]);
    $goodItem = Item::factory()->create(['name' => 'Can']);
    $excludedItem = Item::factory()->create(['name' => 'Piece of <add material>']);

    $goodPhoto = Photo::factory()->create(['user_id' => $user->id]);
    PhotoItem::create(['photo_id' => $goodPhoto->id, 'item_id' => $goodItem->id]);

    $excludedPhoto = Photo::factory()->create(['user_id' => $user->id]);
    PhotoItem::create(['photo_id' => $excludedPhoto->id, 'item_id' => $excludedItem->id]);

    $this->artisan('ml:generate-manifest')->assertExitCode(0);

    $csv = Storage::disk('s3')->get('ml/manifests/manifest_latest.csv');
    $lines = array_values(array_filter(explode("\n", trim((string) $csv))));

    expect($lines)->toHaveCount(2); // header + 1 good photo only
});

it('excludes placeholder brand and content tags from output', function (): void {
    $user = User::factory()->create(['settings' => ['consent_to_training_at' => now()->toIso8601String()]]);
    $item = Item::factory()->create(['name' => 'Bottle']);
    $goodBrand = Tag::factory()->create(['tag_type_id' => $this->brandType->id, 'name' => 'Heineken']);
    $excludedBrand = Tag::factory()->create(['tag_type_id' => $this->brandType->id, 'name' => 'OTHER (Please add this missing brand to the picklist)']);

    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $photoItem = PhotoItem::create(['photo_id' => $photo->id, 'item_id' => $item->id]);
    PhotoItemTag::create(['photo_item_id' => $photoItem->id, 'tag_id' => $goodBrand->id]);
    PhotoItemTag::create(['photo_item_id' => $photoItem->id, 'tag_id' => $excludedBrand->id]);

    $this->artisan('ml:generate-manifest')->assertExitCode(0);

    $csv = Storage::disk('s3')->get('ml/manifests/manifest_latest.csv');
    $lines = array_values(array_filter(explode("\n", trim((string) $csv))));
    $row = str_getcsv($lines[1]);

    expect($row[3])->toBe((string) $goodBrand->id); // Only good brand, not excluded
});

it('supports --limit for smaller end-to-end runs', function (): void {
    $user = User::factory()->create(['settings' => ['consent_to_training_at' => now()->toIso8601String()]]);
    $item = Item::factory()->create(['name' => 'Can']);

    for ($index = 0; $index < 3; $index++) {
        $photo = Photo::factory()->create(['user_id' => $user->id]);
        PhotoItem::create(['photo_id' => $photo->id, 'item_id' => $item->id]);
    }

    $this->artisan('ml:generate-manifest', ['--limit' => 2])->assertExitCode(0);

    $csv = Storage::disk('s3')->get('ml/manifests/manifest_latest.csv');
    $lines = array_values(array_filter(explode("\n", trim((string) $csv))));

    expect($lines)->toHaveCount(3); // header + 2 rows
});

it('handles multiple brand and content tags with pipe separator', function (): void {
    $user = User::factory()->create(['settings' => ['consent_to_training_at' => now()->toIso8601String()]]);
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

    $csv = Storage::disk('s3')->get('ml/manifests/manifest_latest.csv');
    $lines = array_values(array_filter(explode("\n", trim((string) $csv))));
    $row = str_getcsv($lines[1]);

    $brandIds = explode('|', (string) $row[3]);
    expect($brandIds)->toHaveCount(2);
    expect($brandIds)->toContain((string) $brand1->id, (string) $brand2->id);
    expect($row[4])->toBe((string) $content1->id);
});

it('fails when no users have consented', function (): void {
    User::factory()->create(['settings' => ['consent_to_training_at' => null]]);

    $this->artisan('ml:generate-manifest')
        ->expectsOutputToContain('No users have consented')
        ->assertExitCode(1);
});
