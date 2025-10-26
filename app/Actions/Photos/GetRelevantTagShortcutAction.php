<?php

namespace App\Actions\Photos;

use App\Models\TagShortcut;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;

class GetRelevantTagShortcutAction
{
    public function run(User $user, int $itemId): ?TagShortcut
    {
        return TagShortcut::query()
            ->where('user_id', $user->id)
            ->whereHas('items', fn (Builder $q) => $q->where('items.id', $itemId))
            ->with(TagShortcut::commonEagerLoads())
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
    }
}
