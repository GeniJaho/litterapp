<?php

use App\DTO\PhotoFilters;
use App\DTO\UserSettings;
use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\PhotoItemSuggestion;
use App\Models\Tag;
use App\Models\TagShortcut;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

beforeEach(function (): void {
    Storage::fake(config('filesystems.default'));
});

test('a user can see their photos', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create(['created_at' => now()]);
    $photoB = Photo::factory()->for($user)->create(['created_at' => now()->addMinute()]);
    $item = Item::factory()->create();
    $tag = Tag::factory()->create();
    PhotoItem::factory()->for($item)->for($photoB)->create();
    PhotoItemSuggestion::factory()->for($item)->for($photoB)->create(['is_accepted' => null]); // Only consider pending suggestions
    PhotoItemSuggestion::factory()->for($item)->for($photoA)->create(['is_accepted' => true]);
    $emptyTagShortcut = TagShortcut::factory()->create(['user_id' => $user->id]);
    $tagShortcut = TagShortcut::factory()->create(['user_id' => $user->id]);
    $tagShortcut->items()->attach($item, [
        'picked_up' => false,
        'recycled' => false,
        'deposit' => true,
        'quantity' => 3,
    ]);
    $tagShortcut->tagShortcutItems()->first()->tags()->attach($tag);

    $response = $this->get('/my-photos');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->where('photos.data.0.id', $photoB->id)
        ->where('photos.data.0.full_path', $photoB->full_path)
        ->where('photos.data.0.items_exists', true)
        ->where('photos.data.0.photo_item_suggestions_exists', true)
        ->where('photos.data.1.id', $photoA->id)
        ->where('photos.data.1.full_path', $photoA->full_path)
        ->where('photos.data.1.items_exists', false)
        ->where('photos.data.1.photo_item_suggestions_exists', false)
        ->has('tagShortcuts', 1)
        ->where('tagShortcuts.0.id', $tagShortcut->id)
        ->where('tagShortcuts.0.shortcut', $tagShortcut->shortcut)
        ->where('tagShortcuts.0.tag_shortcut_items.0.item.id', $item->id)
        ->where('tagShortcuts.0.tag_shortcut_items.0.item.name', $item->name)
        ->where('tagShortcuts.0.tag_shortcut_items.0.tags.0.id', $tag->id)
        ->where('tagShortcuts.0.tag_shortcut_items.0.tags.0.name', $tag->name)
        ->etc()
    );
});

test('a user can not see another users photos', function (): void {
    $this->actingAs(User::factory()->create());
    $otherUser = User::factory()->create();

    Photo::factory()->for($otherUser)->create();

    $response = $this->get('/my-photos');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->where('photos.data', [])
        ->etc()
    );
});

test('a user can choose the number of photos per page', function (): void {
    $this->actingAs($user = User::factory()->create());
    Photo::factory(26)->for($user)->create();

    $response = $this->get('/my-photos?set_per_page=true&per_page=25');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 25)
        ->where('photos.per_page', 25)
        ->etc()
    );

    $response = $this->get('/my-photos');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 25)
        ->where('photos.per_page', 25)
        ->etc()
    );
});

test('a user can sort their photos by id', function (string $sortDirection): void {
    $this->actingAs($user = User::factory()->create());
    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $photoC = Photo::factory()->for($user)->create();

    $response = $this->get("/my-photos?set_sort=true&sort_column=id&sort_direction={$sortDirection}");

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 3)
        ->where('photos.data.0.id', $sortDirection === 'asc' ? $photoA->id : $photoC->id)
        ->where('photos.data.1.id', $photoB->id)
        ->where('photos.data.2.id', $sortDirection === 'asc' ? $photoC->id : $photoA->id)
        ->etc()
    );
})->with([['asc'], ['desc']]);

test('a user can sort their photos by date taken ascending and id ascending as backup', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photoA = Photo::factory()->for($user)->create(['taken_at_local' => now()]);
    $photoB = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinute()]);
    $photoC = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinutes(2)]);
    $photoD = Photo::factory()->for($user)->create(['taken_at_local' => null]);
    $photoE = Photo::factory()->for($user)->create(['taken_at_local' => null]);

    $response = $this->get('/my-photos?set_sort=true&sort_column=taken_at_local&sort_direction=asc');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')->has('photos.data', 5)
        ->where('photos.data.0.id', $photoD->id) // null
        ->where('photos.data.1.id', $photoE->id) // null
        ->where('photos.data.2.id', $photoA->id)
        ->where('photos.data.3.id', $photoB->id)
        ->where('photos.data.4.id', $photoC->id)
        ->etc()
    );
});

test('a user can sort their photos by date taken descending and id ascending as backup', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photoA = Photo::factory()->for($user)->create(['taken_at_local' => now()]);
    $photoB = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinute()]);
    $photoC = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinutes(2)]);
    $photoD = Photo::factory()->for($user)->create(['taken_at_local' => null]);
    $photoE = Photo::factory()->for($user)->create(['taken_at_local' => null]);

    $response = $this->get('/my-photos?set_sort=true&sort_column=taken_at_local&sort_direction=desc');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')->has('photos.data', 5)
        ->where('photos.data.0.id', $photoC->id)
        ->where('photos.data.1.id', $photoB->id)
        ->where('photos.data.2.id', $photoA->id)
        ->where('photos.data.3.id', $photoD->id) // null
        ->where('photos.data.4.id', $photoE->id) // null
        ->etc()
    );
});

test('a user can sort their photos by file name', function (string $sortDirection): void {
    $this->actingAs($user = User::factory()->create());
    $photoA = Photo::factory()->for($user)->create(['original_file_name' => 'a name']);
    $photoB = Photo::factory()->for($user)->create(['original_file_name' => 'b name']);
    $photoC = Photo::factory()->for($user)->create(['original_file_name' => 'c name']);

    $response = $this->get("/my-photos?set_sort=true&sort_column=original_file_name&sort_direction={$sortDirection}");

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')->has('photos.data', 3)
        ->where('photos.data.0.id', $sortDirection === 'asc' ? $photoA->id : $photoC->id)
        ->where('photos.data.1.id', $photoB->id)
        ->where('photos.data.2.id', $sortDirection === 'asc' ? $photoC->id : $photoA->id)
        ->etc()
    );
})->with([['asc'], ['desc']]);

test('a user can filter their photos by items on the photos', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    PhotoItem::factory()->for($item)->for($photoB)->create();

    $response = $this->get('/my-photos?store_filters=1&item_ids[]='.$item->id);

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->where('photos.data.0.items_exists', true)
        ->etc()
    );
});

test('when the user is filtering the photos the filters are stored in their settings', function (): void {
    $this->actingAs($user = User::factory()->create());
    $item = Item::factory()->create();

    $this->get('/my-photos?store_filters=0&item_ids[]='.$item->id)->assertOk();

    expect($user->fresh()->settings->photo_filters)->toBeNull();

    $this->get('/my-photos?store_filters=1&item_ids[]='.$item->id)->assertOk();

    expect($user->fresh()->settings->photo_filters)->toEqual(new PhotoFilters(
        item_ids: [$item->id],
    ));
});

test('when the user is clearing the filters they are removed in their settings', function (): void {
    $this->actingAs($user = User::factory()->create([
        'settings' => new UserSettings(photo_filters: new PhotoFilters(item_ids: [1])),
    ]));

    $this->get('/my-photos?clear_filters=1')->assertOk();

    expect($user->fresh()->settings->photo_filters)->toBeNull();
});

test('a user can filter their photos by tags on the photo items', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $tag = Tag::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photoB)->create();
    $photoItem->tags()->attach($tag);

    $response = $this->get('/my-photos?store_filters=1&tag_ids[]='.$tag->id);

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->where('photos.data.0.items_exists', true)
        ->etc()
    );
});

test('a user can filter their photos by date uploaded from', function (): void {
    $this->freezeTime();
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create(['created_at' => now()]);
    $photoB = Photo::factory()->for($user)->create(['created_at' => now()->addMinute()]);

    $response = $this->get('/my-photos?store_filters=1&uploaded_from='.now()->addMinute()->format('Y-m-d\TH:i'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->etc()
    );
});

test('a user can filter their photos by date uploaded until', function (): void {
    $this->freezeTime();
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create(['created_at' => now()]);
    $photoB = Photo::factory()->for($user)->create(['created_at' => now()->addMinutes(2)]);

    $response = $this->get('/my-photos?store_filters=1&uploaded_until='.now()->addMinute()->format('Y-m-d\TH:i'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->etc()
    );
});

test('a user can filter their photos by the date the photo is taken from', function (): void {
    $this->freezeTime();
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create(['taken_at_local' => now()]);
    $photoB = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinute()]);

    $response = $this->get('/my-photos?store_filters=1&taken_from_local='.now()->addMinute()->format('Y-m-d\TH:i'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->etc()
    );
});

test('a user can filter their photos by the date the photo is taken until', function (): void {
    $this->freezeTime();
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create(['taken_at_local' => now()]);
    $photoB = Photo::factory()->for($user)->create(['taken_at_local' => now()->addMinutes(2)]);

    $response = $this->get('/my-photos?store_filters=1&taken_until_local='.now()->addMinute()->format('Y-m-d\TH:i'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->etc()
    );
});

test('a user can filter their photos by having GPS data or not', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create(['latitude' => 1, 'longitude' => 1]);
    $photoB = Photo::factory()->for($user)->create(['latitude' => null, 'longitude' => null]);

    $response = $this->get('/my-photos?store_filters=1&has_gps=1');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&has_gps=0');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&has_gps=');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 2)
        ->etc()
    );
});

test('a user can filter their photos by having tags or not', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    PhotoItem::factory()->for($item)->for($photoB)->create();

    $response = $this->get('/my-photos?store_filters=1&is_tagged=1');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&is_tagged=0');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&is_tagged=');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 2)
        ->etc()
    );
});

test('a user can filter their photos by having item suggestions or not', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    PhotoItemSuggestion::factory()->for($item)->for($photoB)->create();

    $response = $this->get('/my-photos?store_filters=1&has_item_suggestions=1');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&has_item_suggestions=0');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&has_item_suggestions=');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 2)
        ->etc()
    );
});

test('a user can filter their photos by being picked up or not', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    PhotoItem::factory()->for($item)->for($photoA)->create([
        'picked_up' => false,
    ]);
    PhotoItem::factory()->for($item)->for($photoB)->create([
        'picked_up' => true,
    ]);

    $response = $this->get('/my-photos?store_filters=1&picked_up=1');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&picked_up=0');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&picked_up=');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 2)
        ->etc()
    );
});

test('a user can filter their photos by being recycled or not', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    PhotoItem::factory()->for($item)->for($photoA)->create([
        'recycled' => false,
    ]);
    PhotoItem::factory()->for($item)->for($photoB)->create([
        'recycled' => true,
    ]);

    $response = $this->get('/my-photos?store_filters=1&recycled=1');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&recycled=0');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&recycled=');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 2)
        ->etc()
    );
});

test('a user can filter their photos by being deposit or not', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photoA = Photo::factory()->for($user)->create();
    $photoB = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    PhotoItem::factory()->for($item)->for($photoA)->create([
        'deposit' => false,
    ]);
    PhotoItem::factory()->for($item)->for($photoB)->create([
        'deposit' => true,
    ]);

    $response = $this->get('/my-photos?store_filters=1&deposit=1');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoB->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&deposit=0');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoA->id)
        ->etc()
    );

    $response = $this->get('/my-photos?store_filters=1&deposit=');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 2)
        ->etc()
    );
});

test('admin can see user selection in the filters', function (): void {
    // Ensure we have predictable admin email configuration
    Config::set('app.admin_emails', ['admin@example.com']);

    // Create an admin user
    $admin = User::factory()->create(['email' => 'admin@example.com']);

    // Create some regular users with photos
    $user1 = User::factory()->create(['name' => 'User One']);
    $user2 = User::factory()->create(['name' => 'User Two']);
    Photo::factory()->for($user1)->create();
    Photo::factory()->for($user2)->create();

    // Act as the admin and visit the photos page
    $response = $this->actingAs($admin)->get('/my-photos');

    // Assert success and check that users are passed to the component
    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('users', 2) // Only users with photos are loaded (not the admin)
        ->where('isAdmin', true)
        ->etc()
    );
});

test('regular user cannot see user selection in the filters', function (): void {
    // Create a regular user
    $user = User::factory()->create();

    // Create another user with photos
    $otherUser = User::factory()->create();
    Photo::factory()->for($otherUser)->create();

    // Act as the regular user and visit the photos page
    $response = $this->actingAs($user)->get('/my-photos');

    // Assert success and check that users array is empty
    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->where('users', [])
        ->where('isAdmin', false)
        ->etc()
    );
});

test('admin can filter photos by a single user', function (): void {
    // Ensure we have predictable admin email configuration
    Config::set('app.admin_emails', ['admin@example.com']);

    // Create an admin user
    $admin = User::factory()->create(['email' => 'admin@example.com']);

    // Create some regular users with photos
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $photo1 = Photo::factory()->for($user1)->create();
    $photo2 = Photo::factory()->for($user2)->create();

    // Act as the admin and filter photos by user1
    $response = $this->actingAs($admin)->get('/my-photos?store_filters=1&user_ids[]='.$user1->id);

    // Assert success and check that only user1's photos are returned
    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photo1->id)
        ->where('filters.user_ids.0', $user1->id)
        ->etc()
    );
});

test('admin can filter photos by multiple users', function (): void {
    // Ensure we have predictable admin email configuration
    Config::set('app.admin_emails', ['admin@example.com']);

    // Create an admin user
    $admin = User::factory()->create(['email' => 'admin@example.com']);

    // Create some regular users with photos
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $photo1 = Photo::factory()->for($user1)->create();
    $photo2 = Photo::factory()->for($user2)->create();
    $photo3 = Photo::factory()->for($user3)->create();

    // Act as the admin and filter photos by user1 and user2
    $response = $this->actingAs($admin)
        ->get('/my-photos?store_filters=1&user_ids[]='.$user1->id.'&user_ids[]='.$user2->id);

    // Assert success and check that user1's and user2's photos are returned (but not user3's)
    $response->assertOk();

    // Verify that we have 2 photos in the response
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 2)
        ->where('filters.user_ids', [$user1->id, $user2->id])
        // Check that the photos belong to the expected users
        ->where('photos.data.0.user_id', fn ($userId) => $userId === $user1->id || $userId === $user2->id)
        ->where('photos.data.1.user_id', fn ($userId) => $userId === $user1->id || $userId === $user2->id)
        ->etc()
    );
});

test('regular user cannot filter photos by user_ids', function (): void {
    // Create regular users
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // Create photos for both users
    $photo1 = Photo::factory()->for($user1)->create();
    $photo2 = Photo::factory()->for($user2)->create();

    // Act as user1 and try to filter by user2
    $response = $this->actingAs($user1)
        ->get('/my-photos?store_filters=1&user_ids[]='.$user2->id);

    // Assert success but check that only user1's photos are returned
    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photo1->id)
        ->etc()
    );
});

test('admin can see all photos when no user filter is applied', function (): void {
    // Ensure we have predictable admin email configuration
    Config::set('app.admin_emails', ['admin@example.com']);

    // Create an admin user
    $admin = User::factory()->create(['email' => 'admin@example.com']);

    // Create some regular users with photos
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $photo1 = Photo::factory()->for($user1)->create();
    $photo2 = Photo::factory()->for($user2)->create();
    $photoAdmin = Photo::factory()->for($admin)->create();

    // Act as the admin and visit photos page without filters
    $response = $this->actingAs($admin)->get('/my-photos');

    // Assert success and check that only admin's photos are shown by default
    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Index')
        ->has('photos.data', 1)
        ->where('photos.data.0.id', $photoAdmin->id)
        ->etc()
    );
});
