<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\PhotoItemTag;
use App\Models\Tag;
use App\Models\User;

test('a user can add tags to photos with a single item', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $item = Item::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photo)->create();
    $tag = Tag::factory()->create();

    $response = $this->actingAs($user)->post('/photos/tags', [
        'photo_ids' => [$photo->id],
        'tag_ids' => [$tag->id],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('bulkAddTagsResult.tags_added', true);
    $this->assertDatabaseHas('photo_item_tag', [
        'photo_item_id' => $photoItem->id,
        'tag_id' => $tag->id,
    ]);
});

test('it skips photos with multiple items', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $itemA = Item::factory()->create();
    $itemB = Item::factory()->create();
    PhotoItem::factory()->for($itemA)->for($photo)->create();
    PhotoItem::factory()->for($itemB)->for($photo)->create();
    $tag = Tag::factory()->create();

    $response = $this->actingAs($user)->post('/photos/tags', [
        'photo_ids' => [$photo->id],
        'tag_ids' => [$tag->id],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('bulkAddTagsResult.photos_with_multiple_items', [$photo->id]);
    $response->assertSessionHas('bulkAddTagsResult.tags_added', false);
    $this->assertDatabaseEmpty('photo_item_tag');
});

test('it skips photos with no items', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $tag = Tag::factory()->create();

    $response = $this->actingAs($user)->post('/photos/tags', [
        'photo_ids' => [$photo->id],
        'tag_ids' => [$tag->id],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('bulkAddTagsResult.photos_with_no_items', [$photo->id]);
    $response->assertSessionHas('bulkAddTagsResult.tags_added', false);
    $this->assertDatabaseEmpty('photo_item_tag');
});

test('it does not add duplicate tags', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $item = Item::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photo)->create();
    $tag = Tag::factory()->create();
    PhotoItemTag::factory()->for($photoItem)->for($tag)->create();

    $response = $this->actingAs($user)->post('/photos/tags', [
        'photo_ids' => [$photo->id],
        'tag_ids' => [$tag->id],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseCount('photo_item_tag', 1);
});

test('it handles a mix of photos with single, multiple, and no items', function (): void {
    $user = User::factory()->create();
    $tag = Tag::factory()->create();

    $singleItemPhoto = Photo::factory()->create(['user_id' => $user->id]);
    $singleItem = Item::factory()->create();
    $singlePhotoItem = PhotoItem::factory()->for($singleItem)->for($singleItemPhoto)->create();

    $multiItemPhoto = Photo::factory()->create(['user_id' => $user->id]);
    PhotoItem::factory()->for(Item::factory())->for($multiItemPhoto)->create();
    PhotoItem::factory()->for(Item::factory())->for($multiItemPhoto)->create();

    $noItemPhoto = Photo::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post('/photos/tags', [
        'photo_ids' => [$singleItemPhoto->id, $multiItemPhoto->id, $noItemPhoto->id],
        'tag_ids' => [$tag->id],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('bulkAddTagsResult.tags_added', true);
    $response->assertSessionHas('bulkAddTagsResult.photos_with_multiple_items', [$multiItemPhoto->id]);
    $response->assertSessionHas('bulkAddTagsResult.photos_with_no_items', [$noItemPhoto->id]);
    $this->assertDatabaseHas('photo_item_tag', [
        'photo_item_id' => $singlePhotoItem->id,
        'tag_id' => $tag->id,
    ]);
    $this->assertDatabaseCount('photo_item_tag', 1);
});

test('it adds tags per photo, not per item type', function (): void {
    $user = User::factory()->create();
    $item = Item::factory()->create();
    $tag = Tag::factory()->create();

    // Photo A already has the tag on its item
    $photoA = Photo::factory()->create(['user_id' => $user->id]);
    $photoItemA = PhotoItem::factory()->for($item)->for($photoA)->create();
    PhotoItemTag::factory()->for($photoItemA)->for($tag)->create();

    // Photo B has the same item type but does NOT have the tag yet
    $photoB = Photo::factory()->create(['user_id' => $user->id]);
    $photoItemB = PhotoItem::factory()->for($item)->for($photoB)->create();

    $response = $this->actingAs($user)->post('/photos/tags', [
        'photo_ids' => [$photoA->id, $photoB->id],
        'tag_ids' => [$tag->id],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('bulkAddTagsResult.tags_added', true);
    // Photo B should have gotten the tag even though Photo A already had it
    $this->assertDatabaseHas('photo_item_tag', [
        'photo_item_id' => $photoItemB->id,
        'tag_id' => $tag->id,
    ]);
    // Photo A should still have exactly 1 copy (no duplicate)
    $this->assertDatabaseCount('photo_item_tag', 2);
});

test('a user cannot add tags to photos of another user', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $otherUser->id]);
    $item = Item::factory()->create();
    PhotoItem::factory()->for($item)->for($photo)->create();
    $tag = Tag::factory()->create();

    $response = $this->actingAs($user)->post('/photos/tags', [
        'photo_ids' => [$photo->id],
        'tag_ids' => [$tag->id],
    ]);

    $response->assertSessionHasErrors('photo_ids');
    $this->assertDatabaseEmpty('photo_item_tag');
});

test('validation requires photo_ids', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/photos/tags', [
        'tag_ids' => [1],
    ]);

    $response->assertSessionHasErrors(['photo_ids']);
});

test('validation requires tag_ids', function (): void {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post('/photos/tags', [
        'photo_ids' => [$photo->id],
    ]);

    $response->assertSessionHasErrors(['tag_ids']);
});
