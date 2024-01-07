<?php

use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\PhotoItemTag;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('a user can delete a photo', function () {
    Storage::fake();
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create(['path' => 'photos/1.jpg']);
    $item = Item::factory()->create();
    $tag = Tag::factory()->create();
    $photoItem = PhotoItem::factory()->for($item)->for($photo)->create();
    $photoItemTag = PhotoItemTag::factory()->for($photoItem)->for($tag)->create();
    Storage::disk('public')->put('photos/1.jpg', 'test');

    $response = $this->delete(route('photos.destroy', $photo));

    $response->assertRedirect(route('my-photos'));
    $this->assertDatabaseMissing('photos', ['id' => $photo->id]);
    $this->assertDatabaseMissing('photo_items', ['id' => $photoItem->id]);
    $this->assertDatabaseMissing('photo_item_tag', ['id' => $photoItemTag->id]);
    Storage::disk('public')->assertMissing('photos/1.jpg');
});