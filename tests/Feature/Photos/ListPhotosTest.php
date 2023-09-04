<?php

use App\Models\Photo;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

test('a user can see the photos list page', function () {
    $this->actingAs($user = User::factory()->create());

    $response = $this->get('/my-photos');

    $response->assertOk();

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Photos')
    );
});

test('a user can see their photos', function () {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create(['created_at' => now()]);
    $photoB = Photo::factory()->for($user)->create(['created_at' => now()->addMinute()]);

    $response = $this->getJson('/photos');

    $response->assertOk();
    $response->assertJson(fn (AssertableJson $json) => $json
        ->where('data.0.id', $photoB->id)
        ->where('data.0.full_path', $photoB->full_path)
        ->where('data.1.id', $photoA->id)
        ->etc()
    );
});
