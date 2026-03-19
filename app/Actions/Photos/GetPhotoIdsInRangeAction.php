<?php

namespace App\Actions\Photos;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class GetPhotoIdsInRangeAction
{
    /**
     * @return array<int>
     */
    public function run(User $user, int $startId, int $endId): array
    {
        return $user
            ->photos()
            ->filter($user->settings->photo_filters)
            ->whereBetween(
                $user->settings->sort_column === 'id' ? 'id' : 'id',
                [$startId, $endId]
            )
            ->when(
                $user->settings->sort_column !== 'id',
                fn (Builder $query) => $query
                    ->orderBy($user->settings->sort_column, $user->settings->sort_direction)
                    ->orderBy('id')
            )
            ->when(
                $user->settings->sort_column === 'id',
                fn (Builder $query) => $query->orderBy('id', $user->settings->sort_direction)
            )
            ->pluck('id')
            ->all();
    }
}
