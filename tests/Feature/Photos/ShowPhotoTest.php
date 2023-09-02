<?php

use App\Models\Photo;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('a user can see the photo tagging page', function () {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();
    $tags = \App\Models\Tag::factory()->count(2)->create();
    $photoTags = \App\Models\Tag::factory()->count(2)->create();
    $photo->tags()->sync($photoTags);

    $response = $this->get("/photos/{$photo->id}");

    $response->assertOk();

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('ShowPhoto')
        ->where('photo.id', $photo->id)
        ->has('photo.tags', 2)
        ->has('tags', 4)
    );
});
