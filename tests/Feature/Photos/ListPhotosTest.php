<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
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
