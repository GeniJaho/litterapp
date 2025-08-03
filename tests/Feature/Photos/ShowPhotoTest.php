<?php

use App\DTO\PhotoFilters;
use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItemSuggestion;
use App\Models\PhotoItemTag;
use App\Models\Tag;
use App\Models\TagShortcut;
use App\Models\TagType;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

beforeEach(function (): void {
    Storage::fake(config('filesystems.default'));
});

test('a user can see the photo tagging page', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();
    $items = Item::factory()->count(2)->create();
    $brand = TagType::factory()->create(['name' => 'Brand']);
    $material = TagType::factory()->create(['name' => 'Material']);
    $brandTags = Tag::factory()->count(2)->sequence(
        ['name' => 'A brand'],
        ['name' => 'B brand'],
    )->create(['tag_type_id' => $brand->id]);
    $materialTags = Tag::factory()->count(3)->sequence(
        ['name' => 'B material'],
        ['name' => 'A material'],
        ['name' => 'C material'],
    )->create(['tag_type_id' => $material->id]);

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();

    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Photos/Show')
        ->where('photoId', $photo->id)
        ->where('tags', [
            $brand->slug => $brandTags->sortBy('name')->select(['id', 'name', 'tag_type_id'])->values()->toArray(),
            $material->slug => $materialTags->sortBy('name')->select(['id', 'name', 'tag_type_id'])->values()->toArray(),
        ])
        ->has('items', 2)
        ->where('suggestionsEnabled', false)
    );
});

test('a user has suggestions enabled if they are admins and litterbot service is enabled', function (): void {
    $user = User::factory()->create();
    Config::set('app.admin_emails', [$user->email]);
    Config::set('services.litterbot.enabled', true);
    $photo = Photo::factory()->for($user)->create();

    $response = $this->actingAs($user)->get(route('photos.show', $photo));

    $response->assertOk();

    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('suggestionsEnabled', true)
    );
});

test('a user can see their shortcuts in the photo tagging page', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();
    $tag = Tag::factory()->create();
    $item = Item::factory()->create();
    $emptyTagShortcut = TagShortcut::factory()->create(['user_id' => $user->id]);
    $tagShortcut = TagShortcut::factory()->create(['user_id' => $user->id]);
    $tagShortcut->items()->attach($item, [
        'picked_up' => false,
        'recycled' => false,
        'deposit' => true,
        'quantity' => 3,
    ]);
    $tagShortcut->tagShortcutItems()->first()->tags()->attach($tag);

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();

    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->has('tagShortcuts', 1)
        ->where('tagShortcuts.0.id', $tagShortcut->id)
        ->where('tagShortcuts.0.shortcut', $tagShortcut->shortcut)
        ->where('tagShortcuts.0.tag_shortcut_items.0.item.id', $item->id)
        ->where('tagShortcuts.0.tag_shortcut_items.0.item.name', $item->name)
        ->where('tagShortcuts.0.tag_shortcut_items.0.tags.0.id', $tag->id)
        ->where('tagShortcuts.0.tag_shortcut_items.0.tags.0.name', $tag->name)
    );
});

test('a user can see the next untagged photo link', function (): void {
    $this->actingAs($user = User::factory()->create());
    $user->settings->photo_filters = new PhotoFilters(is_tagged: false);
    $user->save();

    $untaggedPhoto = Photo::factory()->for($user)->create();
    $photo = Photo::factory()->for($user)->create();

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('nextPhotoUrl', route('photos.show', $untaggedPhoto))
    );
});

test('a user can not see the next untagged photo link if there are no more untagged photos', function (): void {
    $this->actingAs($user = User::factory()->create());
    $user->settings->photo_filters = new PhotoFilters(is_tagged: false);
    $user->save();

    $photo = Photo::factory()->for($user)->create();
    $taggedPhoto = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $taggedPhoto->items()->sync($item);

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page->where('nextPhotoUrl', null));
});

test('a user can see the previous untagged photo link', function (): void {
    $this->actingAs($user = User::factory()->create());
    $user->settings->photo_filters = new PhotoFilters(is_tagged: false);
    $user->save();

    $photo = Photo::factory()->for($user)->create();
    $untaggedPhoto = Photo::factory()->for($user)->create();

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('previousPhotoUrl', route('photos.show', $untaggedPhoto))
    );
});

test('a user can not see the previous untagged photo link if there are no more untagged photos', function (): void {
    $this->actingAs($user = User::factory()->create());
    $user->settings->photo_filters = new PhotoFilters(is_tagged: false);
    $user->save();

    $photo = Photo::factory()->for($user)->create();
    $taggedPhoto = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $taggedPhoto->items()->sync($item);

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page->where('previousPhotoUrl', null));
});

test('a user can see the next and previous photo link according to their ordering settings', function (): void {
    $this->actingAs($user = User::factory()->create());
    $user->settings->sort_column = 'taken_at_local';
    $user->settings->sort_direction = 'asc';
    $user->save();

    $previousPhoto = Photo::factory()->for($user)->create(['taken_at_local' => now()->subDay()]);
    $photo = Photo::factory()->for($user)->create(['taken_at_local' => now()]);
    $nextPhoto = Photo::factory()->for($user)->create(['taken_at_local' => now()->addDay()]);

    $response = $this->get(route('photos.show', $photo));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('nextPhotoUrl', route('photos.show', $nextPhoto))
        ->where('previousPhotoUrl', route('photos.show', $previousPhoto))
    );
});

test('a user can see the next and previous photo links in ascending order accounting for null values', function (): void {
    $this->actingAs($user = User::factory()->create());
    $user->settings->sort_column = 'taken_at_local';
    $user->settings->sort_direction = 'asc';
    $user->save();

    // they have incrementing ids
    $photoA = Photo::factory()->for($user)->create(['taken_at_local' => null]);
    $photoB = Photo::factory()->for($user)->create(['taken_at_local' => null]);
    $photoC = Photo::factory()->for($user)->create(['taken_at_local' => now()->subDay()]);
    $photoD = Photo::factory()->for($user)->create(['taken_at_local' => now()]);
    $photoE = Photo::factory()->for($user)->create(['taken_at_local' => now()->addDay()]);
    $photoF = Photo::factory()->for($user)->create(['taken_at_local' => null]);
    $photoG = Photo::factory()->for($user)->create(['taken_at_local' => null]);

    // sorted by taken_at_local asc then id asc, the rank would be
    // photoA, photoB, photoF, photoG, photoC, photoD, photoE

    $response = $this->get(route('photos.show', $photoA));
    $response->assertOk()->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('previousPhotoUrl', null)
        ->where('nextPhotoUrl', route('photos.show', $photoB))
    );

    $response = $this->get(route('photos.show', $photoB));
    $response->assertOk()->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('previousPhotoUrl', route('photos.show', $photoA))
        ->where('nextPhotoUrl', route('photos.show', $photoF))
    );

    $response = $this->get(route('photos.show', $photoF));
    $response->assertOk()->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('previousPhotoUrl', route('photos.show', $photoB))
        ->where('nextPhotoUrl', route('photos.show', $photoG))
    );

    $response = $this->get(route('photos.show', $photoG));
    $response->assertOk()->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('previousPhotoUrl', route('photos.show', $photoF))
        ->where('nextPhotoUrl', route('photos.show', $photoC))
    );

    $response = $this->get(route('photos.show', $photoC));
    $response->assertOk()->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('previousPhotoUrl', route('photos.show', $photoG))
        ->where('nextPhotoUrl', route('photos.show', $photoD))
    );

    $response = $this->get(route('photos.show', $photoD));
    $response->assertOk()->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('previousPhotoUrl', route('photos.show', $photoC))
        ->where('nextPhotoUrl', route('photos.show', $photoE))
    );

    $response = $this->get(route('photos.show', $photoE));
    $response->assertOk()->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('previousPhotoUrl', route('photos.show', $photoD))
        ->where('nextPhotoUrl', null)
    );
});

test('a user can see the next and previous photo links in descending order accounting for null values', function (): void {
    $this->actingAs($user = User::factory()->create());
    $user->settings->sort_column = 'taken_at_local';
    $user->settings->sort_direction = 'desc';
    $user->save();

    // they have incrementing ids
    $photoA = Photo::factory()->for($user)->create(['taken_at_local' => null]);
    $photoB = Photo::factory()->for($user)->create(['taken_at_local' => null]);
    $photoC = Photo::factory()->for($user)->create(['taken_at_local' => now()->subDay()]);
    $photoD = Photo::factory()->for($user)->create(['taken_at_local' => now()]);
    $photoE = Photo::factory()->for($user)->create(['taken_at_local' => now()->addDay()]);
    $photoF = Photo::factory()->for($user)->create(['taken_at_local' => null]);
    $photoG = Photo::factory()->for($user)->create(['taken_at_local' => null]);

    // sorted by taken_at_local desc then id asc, the rank would be
    // photoE, photoD, photoC, photoA, photoB, photoF, photoG

    $response = $this->get(route('photos.show', $photoE));
    $response->assertOk()->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('previousPhotoUrl', null)
        ->where('nextPhotoUrl', route('photos.show', $photoD))
    );

    $response = $this->get(route('photos.show', $photoD));
    $response->assertOk()->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('previousPhotoUrl', route('photos.show', $photoE))
        ->where('nextPhotoUrl', route('photos.show', $photoC))
    );

    $response = $this->get(route('photos.show', $photoC));
    $response->assertOk()->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('previousPhotoUrl', route('photos.show', $photoD))
        ->where('nextPhotoUrl', route('photos.show', $photoA))
    );

    $response = $this->get(route('photos.show', $photoA));
    $response->assertOk()->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('previousPhotoUrl', route('photos.show', $photoC))
        ->where('nextPhotoUrl', route('photos.show', $photoB))
    );

    $response = $this->get(route('photos.show', $photoB));
    $response->assertOk()->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('previousPhotoUrl', route('photos.show', $photoA))
        ->where('nextPhotoUrl', route('photos.show', $photoF))
    );

    $response = $this->get(route('photos.show', $photoF));
    $response->assertOk()->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('previousPhotoUrl', route('photos.show', $photoB))
        ->where('nextPhotoUrl', route('photos.show', $photoG))
    );

    $response = $this->get(route('photos.show', $photoG));
    $response->assertOk()->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->where('previousPhotoUrl', route('photos.show', $photoF))
        ->where('nextPhotoUrl', null)
    );
});

test('a user can see a photo', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $photo->items()->sync($item);
    $tag = Tag::factory()->create();
    PhotoItemTag::create([
        'photo_item_id' => $photo->photoItems()->first()->id,
        'tag_id' => $tag->id,
    ]);
    $photoItemSuggestion = PhotoItemSuggestion::factory()->create([
        'photo_id' => $photo->id,
        'item_id' => $item->id,
    ]);

    $response = $this->getJson(route('photos.show', $photo));

    $response->assertOk();
    $response->assertJson(fn (AssertableJson $json): AssertableJson => $json
        ->where('photo.id', $photo->id)
        ->where('photo.full_path', $photo->full_path)
        ->has('photo.photo_items', 1)
        ->where('photo.photo_items.0.item.id', $item->id)
        ->where('photo.photo_items.0.item.name', $item->name)
        ->has('photo.photo_items.0.tags', 1)
        ->where('photo.photo_items.0.tags.0.id', $tag->id)
        ->where('photo.photo_items.0.tags.0.name', $tag->name)
        ->has('photo.photo_items.0.picked_up')
        ->has('photo.photo_items.0.recycled')
        ->has('photo.photo_items.0.deposit')
        ->has('photo.photo_items.0.quantity')
        ->has('photo.photo_item_suggestions', 1)
        ->where('photo.photo_item_suggestions.0.score', $photoItemSuggestion->score)
        ->where('photo.photo_item_suggestions.0.id', $photoItemSuggestion->id)
        ->where('photo.photo_item_suggestions.0.item.id', $item->id)
        ->where('photo.photo_item_suggestions.0.item.name', $item->name)
        ->etc()
    );
});

test('a user can not see another users photo', function (): void {
    $this->actingAs($user = User::factory()->create());
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->for($otherUser)->create();

    $response = $this->getJson(route('photos.show', $photo));

    $response->assertNotFound();
});
