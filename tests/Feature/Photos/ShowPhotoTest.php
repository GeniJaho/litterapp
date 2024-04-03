<?php

use App\DTO\PhotoFilters;
use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\PhotoItemTag;
use App\Models\Tag;
use App\Models\TagShortcut;
use App\Models\TagType;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

beforeEach(function (): void {
    Storage::fake(config('filesystems.default'));
});

test('a user can see the photo tagging page', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();
    $items = Item::factory()->count(2)->create();
    $deprecatedItem = Item::factory()->create(['deleted_at' => now()]);
    $brand = TagType::factory()->create(['name' => 'Brand']);
    $material = TagType::factory()->create(['name' => 'Material']);
    $brandTags = Tag::factory()->count(2)->sequence(
        ['name' => 'A brand'],
        ['name' => 'B brand'],
    )->create(['tag_type_id' => $brand->id]);
    $materialTags = Tag::factory()->count(3)->sequence(
        ['name' => 'B material'],
        ['name' => 'A material'],
        ['name' => 'C material'],
    )->create(['tag_type_id' => $material->id]);
    $deprecatedTag = Tag::factory()->create(['tag_type_id' => $material->id, 'deleted_at' => now()]);

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();

    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Show')
        ->where('photoId', $photo->id)
        ->where('tags', [
            $brand->slug => $brandTags->sortBy('name')->values()->toArray(),
            $material->slug => $materialTags->sortBy('name')->values()->toArray(),
        ])
        ->has("tags.{$material->slug}", 3)
        ->has('items', 2)
    );
});

test('a user can see their shortcuts in the photo tagging page', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();
    $tag = Tag::factory()->create();
    $item = Item::factory()->create();
    $emptyTagShortcut = TagShortcut::factory()->create(['user_id' => $user->id]);
    $tagShortcut = TagShortcut::factory()->create(['user_id' => $user->id]);
    $tagShortcut->items()->attach($item, [
        'picked_up' => false,
        'recycled' => false,
        'deposit' => true,
        'quantity' => 3,
    ]);
    $tagShortcut->tagShortcutItems()->first()->tags()->attach($tag);

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();

    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->has('tagShortcuts', 1)
        ->where('tagShortcuts.0.id', $tagShortcut->id)
        ->where('tagShortcuts.0.shortcut', $tagShortcut->shortcut)
        ->where('tagShortcuts.0.tag_shortcut_items.0.item.id', $item->id)
        ->where('tagShortcuts.0.tag_shortcut_items.0.item.name', $item->name)
        ->where('tagShortcuts.0.tag_shortcut_items.0.tags.0.id', $tag->id)
        ->where('tagShortcuts.0.tag_shortcut_items.0.tags.0.name', $tag->name)
    );
});

test('a user can see the next untagged photo link', function (): void {
    $this->actingAs($user = User::factory()->create());
    $user->settings->photo_filters = new PhotoFilters(is_tagged: false);
    $user->save();

    $untaggedPhoto = Photo::factory()->for($user)->create();
    $photo = Photo::factory()->for($user)->create();

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('nextPhotoUrl', route('photos.show', $untaggedPhoto))
    );
});

test('a user can not see the next untagged photo link if there are no more untagged photos', function (): void {
    $this->actingAs($user = User::factory()->create());
    $user->settings->photo_filters = new PhotoFilters(is_tagged: false);
    $user->save();

    $photo = Photo::factory()->for($user)->create();
    $taggedPhoto = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $taggedPhoto->items()->sync($item);

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page->where('nextPhotoUrl', null));
});

test('a user can see the previous untagged photo link', function (): void {
    $this->actingAs($user = User::factory()->create());
    $user->settings->photo_filters = new PhotoFilters(is_tagged: false);
    $user->save();

    $photo = Photo::factory()->for($user)->create();
    $untaggedPhoto = Photo::factory()->for($user)->create();

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('previousPhotoUrl', route('photos.show', $untaggedPhoto))
    );
});

test('a user can not see the previous untagged photo link if there are no more untagged photos', function (): void {
    $this->actingAs($user = User::factory()->create());
    $user->settings->photo_filters = new PhotoFilters(is_tagged: false);
    $user->save();

    $photo = Photo::factory()->for($user)->create();
    $taggedPhoto = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $taggedPhoto->items()->sync($item);

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page->where('previousPhotoUrl', null));
});

test('a user can see a photo', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $deprecatedItem = Item::factory()->create(['deleted_at' => now()]);
    $photoItemA = PhotoItem::factory()->create(['photo_id' => $photo->id, 'item_id' => $item->id]);
    $photoItemB = PhotoItem::factory()->create(['photo_id' => $photo->id, 'item_id' => $deprecatedItem->id]);
    $tag = Tag::factory()->create();
    PhotoItemTag::create(['photo_item_id' => $photoItemA->id, 'tag_id' => $tag->id]);
    $deprecatedTag = Tag::factory()->create(['deleted_at' => now()]);
    PhotoItemTag::create(['photo_item_id' => $photoItemA->id, 'tag_id' => $deprecatedTag->id]);

    $response = $this->getJson(route('photos.show', $photo));

    $response->assertOk();
    $response->assertJson(fn (AssertableJson $json): AssertableJson => $json
        ->where('photo.id', $photo->id)
        ->where('photo.full_path', $photo->full_path)
        ->has('photo.photo_items', 2)
        ->where('photo.photo_items.0.item.id', $deprecatedItem->id)
        ->where('photo.photo_items.0.item.name', $deprecatedItem->name)
        ->where('photo.photo_items.1.item.id', $item->id)
        ->where('photo.photo_items.1.item.name', $item->name)
        ->has('photo.photo_items.1.tags', 2)
        ->where('photo.photo_items.1.tags.0.id', $tag->id)
        ->where('photo.photo_items.1.tags.0.name', $tag->name)
        ->where('photo.photo_items.1.tags.1.id', $deprecatedTag->id)
        ->where('photo.photo_items.1.tags.1.name', $deprecatedTag->name)
        ->has('photo.photo_items.0.picked_up')
        ->has('photo.photo_items.0.recycled')
        ->has('photo.photo_items.0.deposit')
        ->has('photo.photo_items.0.quantity')
        ->etc()
    );
});

test('a user can not see another users photo', function (): void {
    $this->actingAs($user = User::factory()->create());
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->for($otherUser)->create();

    $response = $this->getJson(route('photos.show', $photo));

    $response->assertNotFound();
});
