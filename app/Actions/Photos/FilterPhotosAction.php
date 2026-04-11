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
    public function idsUpToPage(User $user, int $limit): array
    {
        return $this->baseQuery($user)->take($limit)->pluck('id')->all();
    }

    /**
     * @return HasMany<Photo, User>|Builder<Photo>
     */
    private function baseQuery(User $user): HasMany|Builder
    {
        $filters = $user->settings->photo_filters;
        $userIds = $filters !== null ? $filters->user_ids : [];

        /** @var HasMany<Photo, User>|Builder<Photo> $query */
        $query = $user->is_admin && $userIds !== []
            ? Photo::query()->whereIn('user_id', $userIds)
            : $user->photos();

        $query
            ->filter($filters)
            ->orderBy($user->settings->sort_column, $user->settings->sort_direction);

        if ($user->settings->sort_column !== 'id') {
            $query->orderBy('id');
        }

        if ($user->is_admin && $userIds !== []) {
            $query->with('user:id,name,profile_photo_path');
        }

        return $query;
    }
}
