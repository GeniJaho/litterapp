<?php

namespace App\Jobs;

use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\PhotoItemTag;
use App\Models\TagShortcutItem;
use App\Models\TagShortcutItemTag;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CopyDefaultTagShortcutsAndPhotosJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly User $user) {}

    public function handle(): void
    {
        $sourceUser = User::where('email', 'shortcut@litterapp.net')
            ->with(['tagShortcuts.tagShortcutItems.tagShortcutItemTags', 'photos.photoItems.photoItemTags'])
            ->first();

        if ($sourceUser === null) {
            return;
        }

        $this->copyTagShortcuts($sourceUser);

        $this->copyPhotos($sourceUser);
    }

    private function copyTagShortcuts(User $sourceUser): void
    {
        foreach ($sourceUser->tagShortcuts as $tagShortcut) {
            if ($this->user->tagShortcuts()->where('shortcut', $tagShortcut->shortcut)->exists()) {
                continue;
            }

            $newShortcut = $tagShortcut->replicate();
            $newShortcut->user_id = $this->user->id;
            $newShortcut->save();

            // copy the tag_shortcut_items
            $tagShortcut->tagShortcutItems->each(function (TagShortcutItem $item) use ($newShortcut): void {
                $newItem = $item->replicate();
                $newItem->tag_shortcut_id = $newShortcut->id;
                $newItem->save();

                // copy the tags
                $item->tagShortcutItemTags->each(function (TagShortcutItemTag $tag) use ($newItem): void {
                    $newTag = $tag->replicate();
                    $newTag->tag_shortcut_item_id = $newItem->id;
                    $newTag->save();
                });
            });
        }
    }

    private function copyPhotos(User $sourceUser): void
    {
        foreach ($sourceUser->photos as $photo) {
            $content = Storage::get($photo->path);

            if ($content === null) {
                continue;
            }

            $newPath = 'photos/'.Str::uuid()->toString().'.'.pathinfo((string) $photo->path, PATHINFO_EXTENSION);

            Storage::put($newPath, $content);

            $newPhoto = Photo::create([
                'user_id' => $this->user->id,
                'path' => $newPath,
                'original_file_name' => $photo->original_file_name,
                'latitude' => $photo->latitude,
                'longitude' => $photo->longitude,
                'taken_at_local' => $photo->taken_at_local,
            ]);

            // copy the photo_items
            $photo->photoItems->each(function (PhotoItem $item) use ($newPhoto): void {
                $newItem = $item->replicate();
                $newItem->photo_id = $newPhoto->id;
                $newItem->save();

                // copy the tags
                $item->photoItemTags->each(function (PhotoItemTag $tag) use ($newItem): void {
                    $newTag = $tag->replicate();
                    $newTag->photo_item_id = $newItem->id;
                    $newTag->save();
                });
            });
        }
    }
}
