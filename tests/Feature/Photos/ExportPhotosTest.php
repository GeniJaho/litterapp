<?php

use App\Models\Photo;
use App\Models\User;

test('a user can export their photos', function (): void {
    $this->actingAs($user = User::factory()->create());

    Photo::factory()->for($user)->create(['created_at' => now()]);

    $response = $this->get('/photos/export');

    $response->assertOk();
    $response->assertDownload('photos.json');
});

test('if there are no photos the export still works', function (): void {
    $this->actingAs($user = User::factory()->create());

    $response = $this->get('/photos/export');

    $response->assertOk();
    $response->assertDownload('photos.json');
});
