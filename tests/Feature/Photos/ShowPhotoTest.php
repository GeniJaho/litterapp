<?php

use App\DTO\PhotoFilters;
use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItemTag;
use App\Models\Tag;
use App\Models\TagType;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    Storage::fake(config('filesystems.default'));
});

test('a user can see the photo tagging page', function () {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();
    $items = Item::factory()->count(2)->create();
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

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Photo/Show')
        ->where('photoId', $photo->id)
        ->where('tags', [
            $brand->slug => $brandTags->sortBy('name')->values()->toArray(),
            $material->slug => $materialTags->sortBy('name')->values()->toArray(),
        ])
        ->has('items', 2)
    );
});

test('a user can see the next untagged photo link', function () {
    $this->actingAs($user = User::factory()->create());
    $user->settings->photo_filters = new PhotoFilters(is_tagged: false);
    $user->save();

    $untaggedPhoto = Photo::factory()->for($user)->create();
    $photo = Photo::factory()->for($user)->create();

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->where('nextPhotoUrl', route('photos.show', $untaggedPhoto))
    );
});

test('a user can not see the next untagged photo link if there are no more untagged photos', function () {
    $this->actingAs($user = User::factory()->create());
    $user->settings->photo_filters = new PhotoFilters(is_tagged: false);
    $user->save();

    $photo = Photo::factory()->for($user)->create();
    $taggedPhoto = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $taggedPhoto->items()->sync($item);

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page->where('nextPhotoUrl', null));
});

test('a user can see the previous untagged photo link', function () {
    $this->actingAs($user = User::factory()->create());
    $user->settings->photo_filters = new PhotoFilters(is_tagged: false);
    $user->save();

    $photo = Photo::factory()->for($user)->create();
    $untaggedPhoto = Photo::factory()->for($user)->create();

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->where('previousPhotoUrl', route('photos.show', $untaggedPhoto))
    );
});

test('a user can not see the previous untagged photo link if there are no more untagged photos', function () {
    $this->actingAs($user = User::factory()->create());
    $user->settings->photo_filters = new PhotoFilters(is_tagged: false);
    $user->save();

    $photo = Photo::factory()->for($user)->create();
    $taggedPhoto = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $taggedPhoto->items()->sync($item);

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page->where('previousPhotoUrl', null));
});

test('a user can see a photo', function () {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();
    $items = Item::factory()->count(2)->create();
    $photo->items()->sync($items);
    $tag = Tag::factory()->create();
    PhotoItemTag::create([
        'photo_item_id' => $photo->items()->orderByDesc('photo_items.id')->first()->pivot->id,
        'tag_id' => $tag->id,
    ]);

    $response = $this->getJson(route('photos.show', $photo));

    $response->assertOk();
    $response->assertJson(fn (AssertableJson $json) => $json
        ->where('photo.id', $photo->id)
        ->where('photo.full_path', $photo->full_path)
        ->has('items', 2)
        ->has('items.0.pivot.tags', 1)
        ->has('items.0.pivot.picked_up')
        ->has('items.0.pivot.recycled')
        ->has('items.0.pivot.deposit')
        ->has('items.0.pivot.quantity')
        ->etc()
    );
});

test('a user can not see another users photo', function () {
    $this->actingAs($user = User::factory()->create());
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->for($otherUser)->create();

    $response = $this->getJson(route('photos.show', $photo));

    $response->assertNotFound();
});
