<?php

namespace App\Actions\TagShortcuts;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class GetTagShortcutsForTaggingAction
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function run(User $user): array
    {
        $itemsByShortcut = $this->itemsByShortcut($user, $this->tagsByShortcutItem($user));

        return DB::table('tag_shortcuts')
            ->select(['id', 'shortcut'])
            ->where('user_id', $user->id)
            ->whereIn('id', array_keys($itemsByShortcut))
            ->orderBy('shortcut')
            ->get()
            ->map(fn (stdClass $shortcut): array => [
                'id' => $shortcut->id,
                'shortcut' => $shortcut->shortcut,
                'tag_shortcut_items' => $itemsByShortcut[$shortcut->id] ?? [],
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<array-key, array<int, array<string, mixed>>>  $tagsByShortcutItem
     * @return array<array-key, array<int, array<string, mixed>>>
     */
    private function itemsByShortcut(User $user, array $tagsByShortcutItem): array
    {
        return DB::table('tag_shortcut_items')
            ->join('tag_shortcuts', 'tag_shortcuts.id', '=', 'tag_shortcut_items.tag_shortcut_id')
            ->join('items', 'items.id', '=', 'tag_shortcut_items.item_id')
            ->where('tag_shortcuts.user_id', $user->id)
            ->orderByDesc('tag_shortcut_items.id')
            ->select([
                'tag_shortcut_items.id',
                'tag_shortcut_items.tag_shortcut_id',
                'tag_shortcut_items.quantity',
                'tag_shortcut_items.picked_up',
                'tag_shortcut_items.recycled',
                'tag_shortcut_items.deposit',
                'items.id as item_id',
                'items.name as item_name',
            ])
            ->get()
            ->groupBy('tag_shortcut_id')
            ->map(fn (Collection $rows): array => $rows
                ->map(fn (stdClass $row): array => [
                    'id' => $row->id,
                    'quantity' => $row->quantity,
                    'picked_up' => (bool) $row->picked_up,
                    'recycled' => (bool) $row->recycled,
                    'deposit' => (bool) $row->deposit,
                    'item' => [
                        'id' => $row->item_id,
                        'name' => $row->item_name,
                    ],
                    'tags' => $tagsByShortcutItem[$row->id] ?? [],
                ])
                ->values()
                ->all()
            )
            ->all();
    }

    /**
     * @return array<array-key, array<int, array<string, mixed>>>
     */
    private function tagsByShortcutItem(User $user): array
    {
        return DB::table('tag_shortcut_item_tag')
            ->join('tag_shortcut_items', 'tag_shortcut_items.id', '=', 'tag_shortcut_item_tag.tag_shortcut_item_id')
            ->join('tag_shortcuts', 'tag_shortcuts.id', '=', 'tag_shortcut_items.tag_shortcut_id')
            ->join('tags', 'tags.id', '=', 'tag_shortcut_item_tag.tag_id')
            ->where('tag_shortcuts.user_id', $user->id)
            ->select([
                'tag_shortcut_item_tag.tag_shortcut_item_id',
                'tags.id',
                'tags.name',
            ])
            ->get()
            ->groupBy('tag_shortcut_item_id')
            ->map(fn (Collection $rows): array => $rows
                ->map(fn (stdClass $row): array => [
                    'id' => $row->id,
                    'name' => $row->name,
                ])
                ->values()
                ->all()
            )
            ->all();
    }
}
