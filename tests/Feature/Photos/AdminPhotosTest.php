<?php

use App\DTO\PhotoFilters;
use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

beforeEach(function (): void {
    Storage::fake(config('filesystems.default'));
    config(['app.admin_emails' => ['admin@test.com']]);
});

function createAdmin(): User
{
    return User::factory()->create(['email' => 'admin@test.com']);
}

test('admin sees all users photos when user_ids filter is set', function (): void {
    $admin = createAdmin();
    $otherUser = User::factory()->create();

    $adminPhoto = Photo::factory()->for($admin)->create();
    $otherPhoto = Photo::factory()->for($otherUser)->create();

    $admin->settings->photo_filters = new PhotoFilters(user_ids: [$admin->id, $otherUser->id]);
    $admin->save();

    $response = $this->actingAs($admin)->get('/my-photos');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 2)
        ->has('users')
        ->etc()
    );
});

test('admin sees only own photos without user_ids filter', function (): void {
    $admin = createAdmin();
    $otherUser = User::factory()->create();

    Photo::factory()->for($admin)->create();
    Photo::factory()->for($otherUser)->create();

    $response = $this->actingAs($admin)->get('/my-photos');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->etc()
    );
});

test('admin can filter by specific users', function (): void {
    $admin = createAdmin();
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    Photo::factory()->for($admin)->create();
    $photoA = Photo::factory()->for($userA)->create();
    Photo::factory()->for($userB)->create();

    $admin->settings->photo_filters = new PhotoFilters(user_ids: [$userA->id]);
    $admin->save();

    $response = $this->actingAs($admin)->get('/my-photos');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->where('photos.data.0.user.name', $userA->name)
        ->etc()
    );
});

test('non-admin user_ids filter is silently ignored', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Photo::factory()->for($user)->create();
    Photo::factory()->for($otherUser)->create();

    $user->settings->photo_filters = new PhotoFilters(user_ids: [$user->id, $otherUser->id]);
    $user->save();

    $response = $this->actingAs($user)->get('/my-photos');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('users', [])
        ->etc()
    );
});

test('admin can view another users photo', function (): void {
    $admin = createAdmin();
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->for($otherUser)->create();

    $response = $this->actingAs($admin)->getJson(route('photos.show', $photo));

    $response->assertOk();
    $response->assertJson(fn (AssertableJson $json): AssertableJson => $json
        ->where('photo.id', $photo->id)
        ->where('photo.user.name', $otherUser->name)
        ->etc()
    );
});

test('admin can delete another users photo', function (): void {
    $admin = createAdmin();
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->for($otherUser)->create(['path' => 'photos/1.jpg']);
    Storage::put('photos/1.jpg', 'test');

    $response = $this->actingAs($admin)->delete(route('photos.destroy', $photo));

    $response->assertRedirect(route('my-photos'));
    $this->assertDatabaseMissing('photos', ['id' => $photo->id]);
    Storage::assertMissing('photos/1.jpg');
});

test('admin can add items to another users photo', function (): void {
    $admin = createAdmin();
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->for($otherUser)->create();
    $item = Item::factory()->create();

    $response = $this->actingAs($admin)->postJson("/photos/{$photo->id}/items", [
        'item_ids' => [$item->id],
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('photo_items', [
        'photo_id' => $photo->id,
        'item_id' => $item->id,
    ]);
});

test('admin can update photo items on another users photo', function (): void {
    $admin = createAdmin();
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->for($otherUser)->create();
    $item = Item::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photo)->create(['quantity' => 1]);

    $response = $this->actingAs($admin)->postJson("/photo-items/{$photoItem->id}", [
        'quantity' => 5,
    ]);

    $response->assertOk();

    expect($photoItem->refresh()->quantity)->toBe(5);
});

test('admin can delete photo items on another users photo', function (): void {
    $admin = createAdmin();
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->for($otherUser)->create();
    $item = Item::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photo)->create();

    $response = $this->actingAs($admin)->deleteJson("/photo-items/{$photoItem->id}");

    $response->assertOk();
    $this->assertDatabaseMissing('photo_items', ['id' => $photoItem->id]);
});

test('admin can add tags to another users photo items', function (): void {
    $admin = createAdmin();
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->for($otherUser)->create();
    $item = Item::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photo)->create();
    $tag = Tag::factory()->create();

    $response = $this->actingAs($admin)->postJson("/photo-items/{$photoItem->id}/tags", [
        'tag_ids' => [$tag->id],
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('photo_item_tag', [
        'photo_item_id' => $photoItem->id,
        'tag_id' => $tag->id,
    ]);
});

test('admin can remove tags from another users photo items', function (): void {
    $admin = createAdmin();
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->for($otherUser)->create();
    $item = Item::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photo)->create();
    $tag = Tag::factory()->create();
    $photoItem->tags()->attach($tag);

    $response = $this->actingAs($admin)->deleteJson("/photo-items/{$photoItem->id}/tags/{$tag->id}");

    $response->assertOk();
    $this->assertDatabaseMissing('photo_item_tag', [
        'photo_item_id' => $photoItem->id,
        'tag_id' => $tag->id,
    ]);
});

test('admin can copy items on another users photo', function (): void {
    $admin = createAdmin();
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->for($otherUser)->create();
    $item = Item::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photo)->create();

    $response = $this->actingAs($admin)->postJson("/photo-items/{$photoItem->id}/copy");

    $response->assertOk();

    expect(PhotoItem::where('photo_id', $photo->id)->where('item_id', $item->id)->count())->toBe(2);
});

test('admin can bulk add items to photos from different users', function (): void {
    $admin = createAdmin();
    $otherUser = User::factory()->create();
    $adminPhoto = Photo::factory()->for($admin)->create();
    $otherPhoto = Photo::factory()->for($otherUser)->create();
    $item = Item::factory()->create();

    $response = $this->actingAs($admin)->postJson('/photos/items', [
        'photo_ids' => [$adminPhoto->id, $otherPhoto->id],
        'items' => [
            [
                'id' => $item->id,
                'picked_up' => true,
                'recycled' => false,
                'deposit' => false,
                'quantity' => 1,
                'tag_ids' => [],
            ],
        ],
        'used_shortcuts' => [],
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('photo_items', ['photo_id' => $adminPhoto->id, 'item_id' => $item->id]);
    $this->assertDatabaseHas('photo_items', ['photo_id' => $otherPhoto->id, 'item_id' => $item->id]);
});

test('admin can bulk delete items from photos of different users', function (): void {
    $admin = createAdmin();
    $otherUser = User::factory()->create();
    $adminPhoto = Photo::factory()->for($admin)->create();
    $otherPhoto = Photo::factory()->for($otherUser)->create();
    $item = Item::factory()->create();
    PhotoItem::factory()->for($item)->for($adminPhoto)->create();
    PhotoItem::factory()->for($item)->for($otherPhoto)->create();

    $response = $this->actingAs($admin)->deleteJson('/photos/items', [
        'photo_ids' => [$adminPhoto->id, $otherPhoto->id],
        'item_ids' => [$item->id],
        'tag_ids' => [],
    ]);

    $response->assertOk();
    $this->assertDatabaseMissing('photo_items', ['photo_id' => $adminPhoto->id]);
    $this->assertDatabaseMissing('photo_items', ['photo_id' => $otherPhoto->id]);
});

test('admin next and previous navigation works with user_ids filter', function (): void {
    $admin = createAdmin();
    $otherUser = User::factory()->create();

    $photoA = Photo::factory()->for($otherUser)->create();
    $photoB = Photo::factory()->for($otherUser)->create();

    $admin->settings->photo_filters = new PhotoFilters(user_ids: [$otherUser->id]);
    $admin->save();

    // Default sort is id desc, so photoB (higher id) is first, photoA is next
    $response = $this->actingAs($admin)->get(route('photos.show', $photoB));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Show')
        ->where('nextPhotoUrl', route('photos.show', $photoA))
        ->where('previousPhotoUrl', null)
    );
});

test('admin photo ids endpoint returns ids from filtered users', function (): void {
    $admin = createAdmin();
    $otherUser = User::factory()->create();

    $adminPhoto = Photo::factory()->for($admin)->create();
    $otherPhoto = Photo::factory()->for($otherUser)->create();

    $admin->settings->photo_filters = new PhotoFilters(user_ids: [$otherUser->id]);
    $admin->save();

    $response = $this->actingAs($admin)->getJson('/my-photos/ids?limit=100');

    $response->assertOk();

    $ids = $response->json();
    expect($ids)->toContain($otherPhoto->id);
    expect($ids)->not->toContain($adminPhoto->id);
});

test('user name is included in photo data when admin has user_ids filter', function (): void {
    $admin = createAdmin();
    $otherUser = User::factory()->create();

    Photo::factory()->for($otherUser)->create();

    $admin->settings->photo_filters = new PhotoFilters(user_ids: [$otherUser->id]);
    $admin->save();

    $response = $this->actingAs($admin)->get('/my-photos');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.user.id', $otherUser->id)
        ->where('photos.data.0.user.name', $otherUser->name)
        ->etc()
    );
});

test('admin can store user_ids filter via request', function (): void {
    $admin = createAdmin();
    $otherUser = User::factory()->create();

    Photo::factory()->for($otherUser)->create();

    $response = $this->actingAs($admin)->get("/my-photos?store_filters=1&user_ids[]={$otherUser->id}");

    $response->assertOk();

    expect($admin->refresh()->settings->photo_filters->user_ids)->toBe([$otherUser->id]);
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->etc()
    );
});

test('users list is passed to admin but not regular users', function (): void {
    $admin = createAdmin();
    $regularUser = User::factory()->create();

    $this->actingAs($admin)->get('/my-photos')
        ->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
            ->has('users', 2)
            ->etc()
        );

    $this->actingAs($regularUser)->get('/my-photos')
        ->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
            ->where('users', [])
            ->etc()
        );
});

test('admin can apply a tag shortcut to another users photo', function (): void {
    $admin = createAdmin();
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->for($otherUser)->create();
    $item = Item::factory()->create();
    $tagShortcut = TagShortcut::factory()->create(['user_id' => $admin->id]);
    $tagShortcut->items()->attach($item, [
        'picked_up' => true,
        'recycled' => false,
        'deposit' => false,
        'quantity' => 1,
    ]);

    $response = $this->actingAs($admin)->postJson("/photos/{$photo->id}/tag-shortcuts/{$tagShortcut->id}");

    $response->assertOk();
    $this->assertDatabaseHas('photo_items', [
        'photo_id' => $photo->id,
        'item_id' => $item->id,
    ]);
});

test('admin can trigger litterbot suggest on another users photo', function (): void {
    $admin = createAdmin();
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->for($otherUser)->create();
    $item = Item::factory()->create(['name' => 'Bottle']);

    $this->swap(ClassifiesPhoto::class, (new FakeClassifyPhotoAction)->shouldReturnPrediction(
        new PhotoItemPrediction('bottle', 0.95)
    ));

    $response = $this->actingAs($admin)->getJson(route('litterbot.suggest', $photo));

    $response->assertOk();
    $response->assertJson(fn (AssertableJson $json): AssertableJson => $json
        ->has('suggestion.id')
        ->where('suggestion.item.id', $item->id)
        ->etc()
    );
});
