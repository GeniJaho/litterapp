<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItemTag;
use App\Models\Tag;
use App\Models\TagType;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

test('a user can see the photo tagging page', function () {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();
    $items = Item::factory()->count(2)->create();
    $brand = TagType::factory()->create(['name' => 'Brand']);
    $material = TagType::factory()->create(['name' => 'Material']);
    $brandTags = Tag::factory()->count(2)->create(['tag_type_id' => $brand->id]);
    $materialTags = Tag::factory()->count(3)->create(['tag_type_id' => $material->id]);

    $response = $this->get("/photos/{$photo->id}");

    $response->assertOk();

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Photo/Show')
        ->where('photoId', $photo->id)
        ->where('tags', [
            $brand->slug => $brandTags->toArray(),
            $material->slug => $materialTags->toArray(),
        ])
        ->has('items', 2)
    );
});

test('a user can see a photo', function () {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();
    $items = Item::factory()->count(2)->create();
    $photo->items()->sync($items);
    $tag = Tag::factory()->create();
    PhotoItemTag::create([
        'photo_item_id' => $photo->items()->first()->pivot->id,
        'tag_id' => $tag->id,
    ]);

    $response = $this->getJson("/photos/{$photo->id}");

    $response->assertOk();
    $response->assertJson(fn (AssertableJson $json) => $json
        ->where('id', $photo->id)
        ->where('full_path', $photo->full_path)
        ->has('items', 2)
        ->has('items.0.pivot.tags', 1)
        ->etc()
    );
});
