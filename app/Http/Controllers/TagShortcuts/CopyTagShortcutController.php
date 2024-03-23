<?php

namespace App\Http\Controllers\TagShortcuts;

use App\Http\Controllers\Controller;
use App\Models\TagShortcut;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CopyTagShortcutController extends Controller
{
    public function __invoke(TagShortcut $tagShortcut): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->id !== (int) $tagShortcut->user_id) {
            abort(404);
        }

        $tagShortcut->load('tagShortcutItems.tags');

        DB::transaction(function () use ($user, $tagShortcut) {
            /** @var TagShortcut $newTagShortcut */
            $newTagShortcut = $user->tagShortcuts()->create([
                'shortcut' => $this->getNewName($tagShortcut),
            ]);

            foreach ($tagShortcut->tagShortcutItems as $tagShortcutItem) {
                $newTagShortcutItem = $tagShortcutItem->replicate();
                $newTagShortcutItem->tag_shortcut_id = $newTagShortcut->id;
                $newTagShortcutItem->save();

                foreach ($tagShortcutItem->tags as $tag) {
                    $newTagShortcutItem->tags()->attach($tag);
                }
            }
        });

        return response()->json();
    }

    private function getNewName(TagShortcut $tagShortcut): string
    {
        $iteration = 1;
        $newName = "{$tagShortcut->shortcut} (copy)";

        while (TagShortcut::where('shortcut', $newName)->exists()) {
            $newName = "{$tagShortcut->shortcut} (copy) ({$iteration})";
            $iteration++;
        }

        return $newName;
    }
}
