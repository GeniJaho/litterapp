<?php

namespace App\Actions\TagShortcuts;

use App\Models\TagShortcut;
use App\Models\TagShortcutItem;
use App\Models\TagShortcutItemTag;
use App\Models\User;

class CopyUserTagShortcutsAction
{
    /**
     * @return array{copied: int, skipped: int}
     */
    public function run(User $fromUser, User $toUser): array
    {
        $copied = 0;
        $skipped = 0;

        $fromUser->tagShortcuts()->with('tagShortcutItems.tagShortcutItemTags')->get()->each(function (TagShortcut $tagShortcut) use ($toUser, &$copied, &$skipped): void {
            if ($toUser->tagShortcuts()->where('shortcut', $tagShortcut->shortcut)->exists()) {
                $skipped++;

                return;
            }

            $newShortcut = $tagShortcut->replicate();
            $newShortcut->user_id = $toUser->id;
            $newShortcut->save();

            $tagShortcut->tagShortcutItems->each(function (TagShortcutItem $item) use ($newShortcut): void {
                $newItem = $item->replicate();
                $newItem->tag_shortcut_id = $newShortcut->id;
                $newItem->save();

                $item->tagShortcutItemTags->each(function (TagShortcutItemTag $tag) use ($newItem): void {
                    $newTag = $tag->replicate();
                    $newTag->tag_shortcut_item_id = $newItem->id;
                    $newTag->save();
                });
            });

            $copied++;
        });

        return ['copied' => $copied, 'skipped' => $skipped];
    }
}
