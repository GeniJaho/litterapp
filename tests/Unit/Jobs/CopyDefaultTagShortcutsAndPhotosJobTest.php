<?php

use App\Jobs\CopyDefaultTagShortcutsAndPhotosJob;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\PhotoItemTag;
use App\Models\Tag;
use App\Models\TagShortcut;
use App\Models\TagShortcutItem;
use App\Models\TagShortcutItemTag;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('test it copies tag shortcuts from source user', function (): void {
    Storage::fake();
    // Create source user
    $sourceUser = User::factory()->create([
        'email' => 'shortcut@litterapp.net',
    ]);

    // Create some tag shortcuts for source user
    $tagShortcut = TagShortcut::factory()->create([
        'user_id' => $sourceUser->id,
        'shortcut' => 'A',
    ]);

    $item = TagShortcutItem::factory()->create([
        'tag_shortcut_id' => $tagShortcut->id,
    ]);

    TagShortcutItemTag::factory()->create([
        'tag_shortcut_item_id' => $item->id,
    ]);

    // Create target user
    $targetUser = User::factory()->create();

    // Run the job
    $job = new CopyDefaultTagShortcutsAndPhotosJob($targetUser);
    $job->handle();

    // Verify shortcuts were copied
    $this->assertCount(1, $targetUser->tagShortcuts);
    $newShortcut = $targetUser->tagShortcuts->first();
    $this->assertEquals('A', $newShortcut->shortcut);

    $this->assertCount(1, $newShortcut->tagShortcutItems);
    $newItem = $newShortcut->tagShortcutItems->first();

    $this->assertCount(1, $newItem->tagShortcutItemTagS);
});

it('test it does not copy if shortcut already exists', function (): void {
    $sourceUser = User::factory()->create([
        'email' => 'shortcut@litterapp.net',
    ]);

    TagShortcut::factory()->create([
        'user_id' => $sourceUser->id,
        'shortcut' => 'A',
    ]);

    $targetUser = User::factory()->create();
    TagShortcut::factory()->create([
        'user_id' => $targetUser->id,
        'shortcut' => 'A',
    ]);

    $job = new CopyDefaultTagShortcutsAndPhotosJob($targetUser);
    $job->handle();

    $this->assertCount(1, $targetUser->tagShortcuts);
});

it('test it does nothing if source user missing', function (): void {
    $targetUser = User::factory()->create();

    $job = new CopyDefaultTagShortcutsAndPhotosJob($targetUser);
    $job->handle();

    $this->assertCount(0, $targetUser->tagShortcuts);
});

it('test it copies photos from source user', function (): void {
    Storage::fake();

    $sourceUser = User::factory()->create([
        'email' => 'shortcut@litterapp.net',
    ]);

    $photo = Photo::factory()->create([
        'user_id' => $sourceUser->id,
        'path' => 'photos/test.jpg',
    ]);

    $photoItem = PhotoItem::factory()->create([
        'photo_id' => $photo->id,
    ]);

    PhotoItemTag::factory()->create([
        'photo_item_id' => $photoItem->id,
        'tag_id' => Tag::factory(),
    ]);

    Storage::put('photos/test.jpg', 'fake content');

    $targetUser = User::factory()->create();

    $job = new CopyDefaultTagShortcutsAndPhotosJob($targetUser);
    $job->handle();

    $this->assertCount(1, $targetUser->photos);
    $newPhoto = $targetUser->photos->first();
    $this->assertEquals($photo->original_file_name, $newPhoto->original_file_name);
    $this->assertNotEquals($photo->path, $newPhoto->path);
    $this->assertTrue(Storage::exists($newPhoto->path));
    $this->assertEquals('fake content', Storage::get($newPhoto->path));

    $this->assertCount(1, $newPhoto->photoItems);
    $newPhotoItem = $newPhoto->photoItems->first();
    $this->assertCount(1, $newPhotoItem->photoItemTags);
});

it('test it continues copying other photos if one is missing', function (): void {
    Storage::fake();

    $sourceUser = User::factory()->create([
        'email' => 'shortcut@litterapp.net',
    ]);

    // First photo exists
    $photo1 = Photo::factory()->create([
        'user_id' => $sourceUser->id,
        'path' => 'photos/photo1.jpg',
    ]);
    Storage::put('photos/photo1.jpg', 'content 1');

    // Second photo missing from storage
    $photo2 = Photo::factory()->create([
        'user_id' => $sourceUser->id,
        'path' => 'photos/photo2.jpg',
    ]);

    // Third photo exists
    $photo3 = Photo::factory()->create([
        'user_id' => $sourceUser->id,
        'path' => 'photos/photo3.jpg',
    ]);
    Storage::put('photos/photo3.jpg', 'content 3');

    $targetUser = User::factory()->create();

    $job = new CopyDefaultTagShortcutsAndPhotosJob($targetUser);
    $job->handle();

    // Verify it copied photo1 and photo3, skipping photo2
    $this->assertCount(2, $targetUser->photos);
});
