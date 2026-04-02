<?php

namespace App\Actions\Photos;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FilterPhotosAction
{
    /**
     * @return LengthAwarePaginator<int, Photo>
     */
    public function run(User $user, bool $showAllPhotos = false): LengthAwarePaginator
    {
        $photos = $this->baseQuery($user, $showAllPhotos)
            ->withExists([
                'items',
                'photoItemSuggestions' => fn (Builder $query) => $query->whereNull('is_accepted')->where('score', '>=', 80),
            ])
            ->paginate($user->settings->getValidPerPage())
            ->withQueryString();

        $photos->getCollection()->transform(function (Photo $photo): Photo {
            $photo->append('full_path');

            return $photo;
        });

        return $photos;
    }

    /**
     * @return array<int, mixed>
     */
    public function allIds(User $user, bool $showAllPhotos = false): array
    {
        return $this->baseQuery($user, $showAllPhotos)->pluck('id')->all();
    }

    /**
     * @return HasMany<Photo, User>|\Illuminate\Database\Eloquent\Builder<Photo>
     */
    private function baseQuery(User $user, bool $showAllPhotos = false): HasMany|\Illuminate\Database\Eloquent\Builder
    {
        $query = $showAllPhotos
            ? Photo::query()->whereHas('user')
            : $user->photos();

        $query = $query
            ->filter($user->settings->photo_filters)
            ->orderBy($user->settings->sort_column, $user->settings->sort_direction);

        if ($user->settings->sort_column !== 'id') {
            $query->orderBy('id');
        }

        if ($showAllPhotos) {
            $query->with('user');
        }

        return $query;
    }
}
