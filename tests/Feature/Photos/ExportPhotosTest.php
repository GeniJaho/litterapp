<?php

use App\Models\Photo;
use App\Models\User;

test('a user can export their photos', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photo = Photo::factory()->for($user)->create();

    $response = $this->get('/photos/export');

    $response->assertOk();
    $response->assertDownload('photos.json');
    $response->assertStreamedJsonContent([
        'photos' => [[
            'id' => $photo->id,
            'original_file_name' => $photo->original_file_name,
            'latitude' => $photo->latitude,
            'longitude' => $photo->longitude,
            'taken_at_local' => $photo->taken_at_local,
            'created_at' => $photo->created_at->toIso8601String(),
            'items' => [],
        ]],
    ]);
});

test('if there are no photos the export still works', function (): void {
    $this->actingAs($user = User::factory()->create());

    $response = $this->get('/photos/export');

    $response->assertOk();
    $response->assertDownload('photos.json');
    $response->assertStreamedJsonContent([
        'photos' => [],
    ]);
});
