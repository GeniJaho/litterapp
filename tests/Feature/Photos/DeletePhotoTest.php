<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\PhotoItemTag;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('a user can delete a photo', function (): void {
    Storage::fake(config('filesystems.default'));
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create(['path' => 'photos/1.jpg']);
    $item = Item::factory()->create();
    $tag = Tag::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photo)->create();
    $photoItemTag = PhotoItemTag::factory()->for($photoItem)->for($tag)->create();
    Storage::put('photos/1.jpg', 'test');

    $response = $this->delete(route('photos.destroy', $photo));

    $response->assertRedirect(route('my-photos'));
    $this->assertDatabaseMissing('photos', ['id' => $photo->id]);
    $this->assertDatabaseMissing('photo_items', ['id' => $photoItem->id]);
    $this->assertDatabaseMissing('photo_item_tag', ['id' => $photoItemTag->id]);
    Storage::assertMissing('photos/1.jpg');
});

test('a user can not delete another users photo', function (): void {
    Storage::fake(config('filesystems.default'));
    $user = User::factory()->create();
    $photo = Photo::factory()->for($user)->create(['path' => 'photos/1.jpg']);
    Storage::put('photos/1.jpg', 'test');

    $response = $this->actingAs(User::factory()->create())->delete(route('photos.destroy', $photo));

    $response->assertNotFound();
    $this->assertDatabaseHas('photos', ['id' => $photo->id]);
    Storage::assertExists('photos/1.jpg');
});
