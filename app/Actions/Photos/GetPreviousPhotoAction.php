<?php

namespace App\Actions\Photos;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;

class GetPreviousPhotoAction
{
    public function run(User $user, Photo $photo): ?string
    {
        $attribute = $photo->getAttribute($user->settings->sort_column);

        /** @var Builder $builder */
        $builder = $user
            ->photos()
            ->filter($user->settings->photo_filters);

        if ($attribute) {
            $previousPhoto = $builder->clone()
                ->where($user->settings->sort_column, $user->settings->sort_direction === 'desc' ? '>' : '<', $attribute)
                ->orderBy($user->settings->sort_column, $user->settings->sort_direction === 'desc' ? 'asc' : 'desc')
                ->when($user->settings->sort_column !== 'id', fn (Builder $query) => $query->orderBy('id', 'desc'))
                ->first();

            if (! $previousPhoto && $user->settings->sort_direction === 'asc') {
                $previousPhoto = $builder->clone()
                    ->whereNull($user->settings->sort_column)
                    ->orderBy('id', 'desc')
                    ->first();
            }
        } else {
            $previousPhoto = $builder->clone()
                ->whereNull($user->settings->sort_column)
                ->where('id', '<', $photo->id)
                ->orderBy('id', 'desc')
                ->first();

            if (! $previousPhoto && $user->settings->sort_direction === 'desc') {
                $previousPhoto = $builder->clone()
                    ->whereNotNull($user->settings->sort_column)
                    ->orderBy($user->settings->sort_column)
                    ->first();
            }
        }

        if (! $previousPhoto) {
            return null;
        }

        return route('photos.show', $previousPhoto);
    }
}
