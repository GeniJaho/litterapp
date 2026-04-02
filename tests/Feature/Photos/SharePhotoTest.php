<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

beforeEach(function (): void {
    Storage::fake(config('filesystems.default'));
});

test('a user can generate a share link for their photo', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();

    $this->postJson("/photos/{$photo->id}/share")
        ->assertSuccessful()
        ->assertJsonStructure(['share_url', 'share_token']);

    expect($photo->refresh()->share_token)->not->toBeNull();
});

test('generating a share link twice returns the same token', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create(['share_token' => 'existing-token']);

    $response = $this->postJson("/photos/{$photo->id}/share")->assertSuccessful();

    expect($photo->refresh()->share_token)->toBe('existing-token');
    expect($response->json('share_token'))->toBe('existing-token');
});

test('a user cannot generate a share link for another users photo', function (): void {
    $this->actingAs(User::factory()->create());
    $photo = Photo::factory()->create();

    $this->postJson("/photos/{$photo->id}/share")->assertNotFound();
});

test('a guest cannot generate a share link', function (): void {
    $photo = Photo::factory()->create();

    $this->postJson("/photos/{$photo->id}/share")->assertUnauthorized();
});

test('a guest can view a shared photo', function (): void {
    $photo = Photo::factory()->create(['share_token' => 'test-token']);
    $item = Item::factory()->create();
    $photo->items()->attach($item);

    $this->get('/s/test-token')
        ->assertSuccessful()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Photos/Share/Show')
            ->has('photo')
            ->where('photo.id', $photo->id)
        );
});

test('viewing a shared photo increments the view count', function (): void {
    $photo = Photo::factory()->create([
        'share_token' => 'test-token',
        'share_view_count' => 0,
    ]);

    $this->get('/s/test-token')->assertSuccessful();

    expect($photo->refresh()->share_view_count)->toBe(1);
});

test('viewing a shared photo does not expose user data', function (): void {
    $photo = Photo::factory()->create(['share_token' => 'test-token']);

    $response = $this->get('/s/test-token')->assertSuccessful();

    $response->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
        ->component('Photos/Share/Show')
        ->missing('photo.user')
    );
});

test('an invalid share token returns 404', function (): void {
    $this->get('/s/nonexistent-token')->assertNotFound();
});

test('an expired share link returns 403', function (): void {
    Photo::factory()->create([
        'share_token' => 'expired-token',
        'share_expires_at' => now()->subDay(),
    ]);

    $this->get('/s/expired-token')->assertForbidden();
});

test('a share link without expiry is always accessible', function (): void {
    Photo::factory()->create([
        'share_token' => 'no-expiry-token',
        'share_expires_at' => null,
    ]);

    $this->get('/s/no-expiry-token')->assertSuccessful();
});
