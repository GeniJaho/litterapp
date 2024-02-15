<?php

use App\DTO\PhotoFilters;
use App\DTO\UserSettings;
use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    Storage::fake(config('filesystems.default'));
});

test('a user can see their photos', function () {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create(['created_at' => now()]);
    $photoB = Photo::factory()->for($user)->create(['created_at' => now()->addMinute()]);
    $item = Item::factory()->create();
    PhotoItem::factory()->for($item)->for($photoB)->create();

    $response = $this->get('/my-photos');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Photos')
        ->where('photos.data.0.id', $photoB->id)
        ->where('photos.data.0.full_path', $photoB->full_path)
        ->where('photos.data.0.items_exists', true)
        ->where('photos.data.1.id', $photoA->id)
        ->where('photos.data.1.full_path', $photoA->full_path)
        ->where('photos.data.1.items_exists', false)
        ->etc()
    );
});

test('a user can not see another users photos', function () {
    $this->actingAs(User::factory()->create());
    $otherUser = User::factory()->create();

    Photo::factory()->for($otherUser)->create();

    $response = $this->get('/my-photos');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Photos')
        ->where('photos.data', [])
        ->etc()
    );
});

test('a user can filter their photos by items on the photos', function () {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    PhotoItem::factory()->for($item)->for($photoB)->create();

    $response = $this->get('/my-photos?store_filters=1&item_ids[]='.$item->id);

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Photos')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->where('photos.data.0.items_exists', true)
        ->etc()
    );
});

test('when the user is filtering the photos the filters are stored in their settings', function () {
    $this->actingAs($user = User::factory()->create());
    $item = Item::factory()->create();

    $this->get('/my-photos?store_filters=0&item_ids[]='.$item->id)->assertOk();

    expect($user->fresh()->settings->photo_filters)->toBeNull();

    $this->get('/my-photos?store_filters=1&item_ids[]='.$item->id)->assertOk();

    expect($user->fresh()->settings->photo_filters)->toEqual(new PhotoFilters(
        item_ids: [$item->id],
    ));
});

test('when the user is clearing the filters they are removed in their settings', function () {
    $this->actingAs($user = User::factory()->create([
        'settings' => new UserSettings(photo_filters: new PhotoFilters(item_ids: [1])),
    ]));

    $this->get('/my-photos?clear_filters=1')->assertOk();

    expect($user->fresh()->settings->photo_filters)->toBeNull();
});

test('a user can filter their photos by tags on the photo items', function () {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $tag = Tag::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photoB)->create();
    $photoItem->tags()->attach($tag);

    $response = $this->get('/my-photos?store_filters=1&tag_ids[]='.$tag->id);

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Photos')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->where('photos.data.0.items_exists', true)
        ->etc()
    );
});

test('a user can filter their photos by date uploaded from', function () {
    $this->freezeTime();
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create(['created_at' => now()]);
    $photoB = Photo::factory()->for($user)->create(['created_at' => now()->addMinute()]);

    $response = $this->get('/my-photos?store_filters=1&uploaded_from='.now()->addSecond()->toDateTimeString());

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Photos')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->etc()
    );
});

test('a user can filter their photos by date uploaded until', function () {
    $this->freezeTime();
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create(['created_at' => now()]);
    $photoB = Photo::factory()->for($user)->create(['created_at' => now()->addMinute()]);

    $response = $this->get('/my-photos?store_filters=1&uploaded_until='.now()->toDateTimeString());

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Photos')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->etc()
    );
});

test('a user can filter their photos by the date the photo is taken from', function () {
    $this->freezeTime();
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create(['taken_at_local' => now()]);
    $photoB = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinute()]);

    $response = $this->get('/my-photos?store_filters=1&taken_from_local='.now()->addSecond()->toDateTimeString());

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Photos')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->etc()
    );
});

test('a user can filter their photos by the date the photo is taken until', function () {
    $this->freezeTime();
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create(['taken_at_local' => now()]);
    $photoB = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinute()]);

    $response = $this->get('/my-photos?store_filters=1&taken_until_local='.now()->toDateTimeString());

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Photos')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->etc()
    );
});

test('a user can filter their photos by having GPS data or not', function () {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create(['latitude' => 1, 'longitude' => 1]);
    $photoB = Photo::factory()->for($user)->create(['latitude' => null, 'longitude' => null]);

    $response = $this->get('/my-photos?store_filters=1&has_gps=1');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Photos')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&has_gps=0');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Photos')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&has_gps=');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Photos')
        ->has('photos.data', 2)
        ->etc()
    );
});

test('a user can filter their photos by having tags or not', function () {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    PhotoItem::factory()->for($item)->for($photoB)->create();

    $response = $this->get('/my-photos?store_filters=1&is_tagged=1');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Photos')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&is_tagged=0');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Photos')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&is_tagged=');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Photos')
        ->has('photos.data', 2)
        ->etc()
    );
});
