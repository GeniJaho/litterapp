<?php

use App\Models\Item;
use App\Models\Tag;
use App\Models\TagType;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('s3');
});

it('generates label maps json and uploads to s3', function (): void {
    $brandType = TagType::factory()->create(['name' => 'Brand', 'slug' => 'brand']);
    $contentType = TagType::factory()->create(['name' => 'Content', 'slug' => 'content']);

    $item = Item::factory()->create(['name' => 'Can']);
    $brand = Tag::factory()->create(['tag_type_id' => $brandType->id, 'name' => 'Red Bull']);
    $content = Tag::factory()->create(['tag_type_id' => $contentType->id, 'name' => 'Energy drink']);

    $this->artisan('ml:generate-label-maps')
        ->assertExitCode(0);

    Storage::disk('s3')->assertExists('ml/manifests/label_maps.json');

    $json = json_decode((string) Storage::disk('s3')->get('ml/manifests/label_maps.json'), true);

    expect($json)->toHaveKeys(['items', 'brands', 'content']);
    expect($json['items'][(string) $item->id])->toBe('Can');
    expect($json['brands'][(string) $brand->id])->toBe('Red Bull');
    expect($json['content'][(string) $content->id])->toBe('Energy drink');
});

it('excludes non-visual items from label maps', function (): void {
    $goodItem = Item::factory()->create(['name' => 'Bottle']);
    $excludedItem = Item::factory()->create(['name' => 'Piece of <add material>']);

    $this->artisan('ml:generate-label-maps')->assertExitCode(0);

    $json = json_decode((string) Storage::disk('s3')->get('ml/manifests/label_maps.json'), true);

    expect($json['items'])->toHaveKey((string) $goodItem->id);
    expect($json['items'])->not->toHaveKey((string) $excludedItem->id);
});

it('excludes placeholder tags from label maps', function (): void {
    $brandType = TagType::factory()->create(['name' => 'Brand', 'slug' => 'brand']);
    $contentType = TagType::factory()->create(['name' => 'Content', 'slug' => 'content']);

    $goodBrand = Tag::factory()->create(['tag_type_id' => $brandType->id, 'name' => 'Heineken']);
    $excludedBrand = Tag::factory()->create(['tag_type_id' => $brandType->id, 'name' => 'OTHER (Please add this missing brand to the picklist)']);
    $excludedContent = Tag::factory()->create(['tag_type_id' => $contentType->id, 'name' => 'OTHER (Please add this missing content to the picklist)']);

    $this->artisan('ml:generate-label-maps')->assertExitCode(0);

    $json = json_decode((string) Storage::disk('s3')->get('ml/manifests/label_maps.json'), true);

    expect($json['brands'])->toHaveKey((string) $goodBrand->id);
    expect($json['brands'])->not->toHaveKey((string) $excludedBrand->id);
    expect($json['content'])->not->toHaveKey((string) $excludedContent->id);
});
