<?php

namespace App\Actions\Photos;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;

class GetNextPhotoAction
{
    public function run(User $user, Photo $photo): ?string
    {
        $attribute = $photo->getAttribute($user->settings->sort_column);

        /** @var Builder $builder */
        $builder = $user
            ->photos()
            ->filter($user->settings->photo_filters);

        if ($attribute) {
            $nextPhoto = $builder->clone()
                ->where($user->settings->sort_column, $user->settings->sort_direction === 'desc' ? '<' : '>', $attribute)
                ->orderBy($user->settings->sort_column, $user->settings->sort_direction)
                ->when($user->settings->sort_column !== 'id', fn (Builder $query) => $query->orderBy('id'))
                ->first();

            if (! $nextPhoto && $user->settings->sort_direction === 'desc') {
                $nextPhoto = $builder->clone()
                    ->whereNull($user->settings->sort_column)
                    ->orderBy('id')
                    ->first();
            }
        } else {
            $nextPhoto = $builder->clone()
                ->whereNull($user->settings->sort_column)
                ->where('id', '>', $photo->id)
                ->orderBy('id')
                ->first();

            if (! $nextPhoto && $user->settings->sort_direction === 'asc') {
                $nextPhoto = $builder->clone()
                    ->whereNotNull($user->settings->sort_column)
                    ->orderBy($user->settings->sort_column, $user->settings->sort_direction)
                    ->when($user->settings->sort_column !== 'id', fn (Builder $query) => $query->orderBy('id'))
                    ->first();
            }
        }

        if (! $nextPhoto) {
            return null;
        }

        return route('photos.show', $nextPhoto);
    }
}
