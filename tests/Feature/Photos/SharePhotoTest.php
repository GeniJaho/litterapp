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
        ->assertJsonStructure(['share_url', 'share_token', 'share_expires_at']);

    expect($photo->refresh()->share_token)->not->toBeNull();
});

test('a user can generate a share link without expiry', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();

    $response = $this->postJson("/photos/{$photo->id}/share")
        ->assertSuccessful();

    expect($response->json('share_expires_at'))->toBeNull();
    expect($photo->refresh()->share_expires_at)->toBeNull();
});

test('a user can generate a share link with 7 day expiry', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();

    $this->freezeTime();

    $response = $this->postJson("/photos/{$photo->id}/share", ['expires_in' => 7])
        ->assertSuccessful();

    expect($response->json('share_expires_at'))->not->toBeNull();
    expect($photo->refresh()->share_expires_at->toDateString())->toBe(now()->addDays(7)->toDateString());
});

test('a user can generate a share link with 30 day expiry', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();

    $this->freezeTime();

    $this->postJson("/photos/{$photo->id}/share", ['expires_in' => 30])
        ->assertSuccessful();

    expect($photo->refresh()->share_expires_at->toDateString())->toBe(now()->addDays(30)->toDateString());
});

test('a user can generate a share link with 90 day expiry', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();

    $this->freezeTime();

    $this->postJson("/photos/{$photo->id}/share", ['expires_in' => 90])
        ->assertSuccessful();

    expect($photo->refresh()->share_expires_at->toDateString())->toBe(now()->addDays(90)->toDateString());
});

test('a user cannot use an invalid expiry value', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();

    $this->postJson("/photos/{$photo->id}/share", ['expires_in' => 5])
        ->assertUnprocessable();
});

test('generating a share link keeps the existing token', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create(['share_token' => 'existing-token']);

    $response = $this->postJson("/photos/{$photo->id}/share")->assertSuccessful();

    expect($photo->refresh()->share_token)->toBe('existing-token');
    expect($response->json('share_token'))->toBe('existing-token');
});

test('updating expiry on an existing share link keeps the same token', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create([
        'share_token' => 'existing-token',
        'share_expires_at' => null,
    ]);

    $this->freezeTime();

    $this->postJson("/photos/{$photo->id}/share", ['expires_in' => 30])->assertSuccessful();

    $photo->refresh();
    expect($photo->share_token)->toBe('existing-token');
    expect($photo->share_expires_at->toDateString())->toBe(now()->addDays(30)->toDateString());
});

test('a user can revoke a share link', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create([
        'share_token' => 'token-to-revoke',
        'share_expires_at' => now()->addDays(7),
    ]);

    $this->deleteJson("/photos/{$photo->id}/share")
        ->assertSuccessful();

    $photo->refresh();
    expect($photo->share_token)->toBeNull();
    expect($photo->share_expires_at)->toBeNull();
});

test('a revoked share link returns 404', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create([
        'share_token' => 'token-to-revoke',
    ]);

    $this->deleteJson("/photos/{$photo->id}/share")->assertSuccessful();

    $this->get('/s/token-to-revoke')->assertNotFound();
});

test('a user cannot revoke a share link for another users photo', function (): void {
    $this->actingAs(User::factory()->create());
    $photo = Photo::factory()->create(['share_token' => 'other-token']);

    $this->deleteJson("/photos/{$photo->id}/share")->assertNotFound();

    expect($photo->refresh()->share_token)->toBe('other-token');
});

test('a guest cannot revoke a share link', function (): void {
    $photo = Photo::factory()->create(['share_token' => 'guest-token']);

    $this->deleteJson("/photos/{$photo->id}/share")->assertUnauthorized();

    expect($photo->refresh()->share_token)->toBe('guest-token');
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
            ->has('photo.photo_items', 1)
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

test('viewing a shared photo only exposes user name and profile photo', function (): void {
    $photo = Photo::factory()->create(['share_token' => 'test-token']);

    $response = $this->get('/s/test-token')->assertSuccessful();

    $response->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
        ->component('Photos/Share/Show')
        ->has('photo.user.name')
        ->has('photo.user.profile_photo_url')
        ->missing('photo.user.email')
        ->missing('photo.user.id')
        ->missing('photo.id')
        ->missing('photo.user_id')
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
