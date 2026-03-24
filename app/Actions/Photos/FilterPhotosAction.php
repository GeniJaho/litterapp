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
    public function run(User $user): LengthAwarePaginator
    {
        $photos = $this->baseQuery($user)
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
    public function allIds(User $user): array
    {
        return $this->baseQuery($user)->pluck('id')->all();
    }

    /**
     * @return HasMany<Photo, User>
     */
    private function baseQuery(User $user): HasMany
    {
        $query = $user
            ->photos()
            ->filter($user->settings->photo_filters)
            ->orderBy($user->settings->sort_column, $user->settings->sort_direction);

        if ($user->settings->sort_column !== 'id') {
            $query->orderBy('id');
        }

        return $query;
    }
}
