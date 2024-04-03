<?php

use App\DTO\PhotoFilters;
use App\DTO\UserSettings;
use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\Tag;
use App\Models\TagType;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

beforeEach(function (): void {
    Storage::fake(config('filesystems.default'));
});

test('a user can see their photos', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create(['created_at' => now()]);
    $photoB = Photo::factory()->for($user)->create(['created_at' => now()->addMinute()]);
    $item = Item::factory()->create();
    PhotoItem::factory()->for($item)->for($photoB)->create();
    $brand = TagType::factory()->create(['slug' => 'brand']);
    $tag = Tag::factory()->create(['tag_type_id' => $brand]);
    $deprecatedTag = Tag::factory()->create(['deleted_at' => now(), 'tag_type_id' => $brand]);
    $deprecatedItem = Item::factory()->create(['deleted_at' => now()]);

    $response = $this->get('/my-photos');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->where('photos.data.0.id', $photoB->id)
        ->where('photos.data.0.full_path', $photoB->full_path)
        ->where('photos.data.0.items_exists', true)
        ->where('photos.data.1.id', $photoA->id)
        ->where('photos.data.1.full_path', $photoA->full_path)
        ->where('photos.data.1.items_exists', false)
        ->has('items', 2)
        ->has('tags.brand', 2)
        ->etc()
    );
});

test('a user can not see another users photos', function (): void {
    $this->actingAs(User::factory()->create());
    $otherUser = User::factory()->create();

    Photo::factory()->for($otherUser)->create();

    $response = $this->get('/my-photos');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->where('photos.data', [])
        ->etc()
    );
});

test('a user can choose the number of photos per page', function (): void {
    $this->actingAs($user = User::factory()->create());
    Photo::factory(26)->for($user)->create();

    $response = $this->get('/my-photos?set_per_page=true&per_page=25');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 25)
        ->where('photos.per_page', 25)
        ->etc()
    );

    $response = $this->get('/my-photos');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 25)
        ->where('photos.per_page', 25)
        ->etc()
    );
});

test('a user can filter their photos by items on the photos', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    PhotoItem::factory()->for($item)->for($photoB)->create();

    $response = $this->get('/my-photos?store_filters=1&item_ids[]='.$item->id);

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->where('photos.data.0.items_exists', true)
        ->etc()
    );
});

test('when the user is filtering the photos the filters are stored in their settings', function (): void {
    $this->actingAs($user = User::factory()->create());
    $item = Item::factory()->create();

    $this->get('/my-photos?store_filters=0&item_ids[]='.$item->id)->assertOk();

    expect($user->fresh()->settings->photo_filters)->toBeNull();

    $this->get('/my-photos?store_filters=1&item_ids[]='.$item->id)->assertOk();

    expect($user->fresh()->settings->photo_filters)->toEqual(new PhotoFilters(
        item_ids: [$item->id],
    ));
});

test('when the user is clearing the filters they are removed in their settings', function (): void {
    $this->actingAs($user = User::factory()->create([
        'settings' => new UserSettings(photo_filters: new PhotoFilters(item_ids: [1])),
    ]));

    $this->get('/my-photos?clear_filters=1')->assertOk();

    expect($user->fresh()->settings->photo_filters)->toBeNull();
});

test('a user can filter their photos by tags on the photo items', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $tag = Tag::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photoB)->create();
    $photoItem->tags()->attach($tag);

    $response = $this->get('/my-photos?store_filters=1&tag_ids[]='.$tag->id);

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->where('photos.data.0.items_exists', true)
        ->etc()
    );
});

test('a user can filter their photos by date uploaded from', function (): void {
    $this->freezeTime();
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create(['created_at' => now()]);
    $photoB = Photo::factory()->for($user)->create(['created_at' => now()->addMinute()]);

    $response = $this->get('/my-photos?store_filters=1&uploaded_from='.now()->addMinute()->format('Y-m-d\TH:i'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->etc()
    );
});

test('a user can filter their photos by date uploaded until', function (): void {
    $this->freezeTime();
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create(['created_at' => now()]);
    $photoB = Photo::factory()->for($user)->create(['created_at' => now()->addMinutes(2)]);

    $response = $this->get('/my-photos?store_filters=1&uploaded_until='.now()->addMinute()->format('Y-m-d\TH:i'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->etc()
    );
});

test('a user can filter their photos by the date the photo is taken from', function (): void {
    $this->freezeTime();
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create(['taken_at_local' => now()]);
    $photoB = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinute()]);

    $response = $this->get('/my-photos?store_filters=1&taken_from_local='.now()->addMinute()->format('Y-m-d\TH:i'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->etc()
    );
});

test('a user can filter their photos by the date the photo is taken until', function (): void {
    $this->freezeTime();
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create(['taken_at_local' => now()]);
    $photoB = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinutes(2)]);

    $response = $this->get('/my-photos?store_filters=1&taken_until_local='.now()->addMinute()->format('Y-m-d\TH:i'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->etc()
    );
});

test('a user can filter their photos by having GPS data or not', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create(['latitude' => 1, 'longitude' => 1]);
    $photoB = Photo::factory()->for($user)->create(['latitude' => null, 'longitude' => null]);

    $response = $this->get('/my-photos?store_filters=1&has_gps=1');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&has_gps=0');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&has_gps=');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 2)
        ->etc()
    );
});

test('a user can filter their photos by having tags or not', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    PhotoItem::factory()->for($item)->for($photoB)->create();

    $response = $this->get('/my-photos?store_filters=1&is_tagged=1');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&is_tagged=0');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&is_tagged=');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 2)
        ->etc()
    );
});

test('a user can filter their photos by being picked up or not', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    PhotoItem::factory()->for($item)->for($photoA)->create([
        'picked_up' => false,
    ]);
    PhotoItem::factory()->for($item)->for($photoB)->create([
        'picked_up' => true,
    ]);

    $response = $this->get('/my-photos?store_filters=1&picked_up=1');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&picked_up=0');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&picked_up=');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 2)
        ->etc()
    );
});

test('a user can filter their photos by being recycled or not', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    PhotoItem::factory()->for($item)->for($photoA)->create([
        'recycled' => false,
    ]);
    PhotoItem::factory()->for($item)->for($photoB)->create([
        'recycled' => true,
    ]);

    $response = $this->get('/my-photos?store_filters=1&recycled=1');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&recycled=0');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&recycled=');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 2)
        ->etc()
    );
});

test('a user can filter their photos by being deposit or not', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    PhotoItem::factory()->for($item)->for($photoA)->create([
        'deposit' => false,
    ]);
    PhotoItem::factory()->for($item)->for($photoB)->create([
        'deposit' => true,
    ]);

    $response = $this->get('/my-photos?store_filters=1&deposit=1');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&deposit=0');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&deposit=');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 2)
        ->etc()
    );
});
