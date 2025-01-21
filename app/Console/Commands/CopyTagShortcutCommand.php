<?php

namespace App\Console\Commands;

use App\Models\TagShortcut;
use App\Models\TagShortcutItem;
use App\Models\TagShortcutItemTag;
use App\Models\User;
use Illuminate\Console\Command;

class CopyTagShortcutCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:copy-tag-shortcut-command {tagShortcutId} {--to=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copies a tag shortcut from a user to another user or users';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var TagShortcut $tagShortcut */
        $tagShortcut = TagShortcut::findOrFail($this->argument('tagShortcutId'));

        $this->components->info("Copying tag shortcut [{$tagShortcut->shortcut}] to user(s)");

        $to = $this->option('to');
        $users = $to ? [User::findOrFail($to)] : User::all();

        /** @var User $user */
        foreach ($users as $user) {
            if ($user->tagShortcuts()->where('shortcut', $tagShortcut->shortcut)->exists()) {
                $this->components->warn("Tag shortcut [{$tagShortcut->shortcut}] already exists for user [{$user->name}]");

                continue;
            }

            $newShortcut = $tagShortcut->replicate();
            $newShortcut->user_id = $user->id;
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

            $this->components->info("Tag shortcut [{$tagShortcut->shortcut}] copied to user [{$user->name}]");
        }

        $this->components->info('Tag shortcut copied successfully');
    }
}
