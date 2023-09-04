<?php

use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('a user can see the photo tagging page', function () {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();
    $tags = Tag::factory()->count(2)->create();
    $photoTags = Tag::factory()->count(2)->create();
    $photo->tags()->sync($photoTags);

    $response = $this->get("/photos/{$photo->id}");

    $response->assertOk();

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('ShowPhoto')
        ->where('photo.id', $photo->id)
        ->where('photo.full_path', $photo->full_path)
        ->has('photo.tags', 2)
        ->has('tags', 4)
    );
});
