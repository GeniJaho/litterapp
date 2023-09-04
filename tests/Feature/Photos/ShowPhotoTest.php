<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

test('a user can see the photo tagging page', function () {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();
    $items = Item::factory()->count(2)->create();
    $tags = Tag::factory()->count(2)->create();

    $response = $this->get("/photos/{$photo->id}");

    $response->assertOk();

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('ShowPhoto')
        ->where('photoId', $photo->id)
        ->has('tags', 2)
        ->has('items', 2)
    );
});

test('a user can see a photo', function () {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();
    $photoItems = Item::factory()->count(2)->create();
    $photo->items()->sync($photoItems);

    $response = $this->getJson("/photos/{$photo->id}");

    $response->assertOk();
    $response->assertJson(fn (AssertableJson $json) => $json
        ->where('id', $photo->id)
        ->where('full_path', $photo->full_path)
        ->has('items', 2)
        ->etc()
    );
});
