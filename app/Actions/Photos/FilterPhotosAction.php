<?php

namespace App\Actions\Photos;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

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
     * @return Builder<Photo>
     */
    private function baseQuery(User $user): Builder
    {
        $filters = $user->settings->photo_filters;

        $query = Photo::query()
            ->forUser($user)
            ->filter($filters)
            ->orderBy($user->settings->sort_column, $user->settings->sort_direction);

        if ($user->settings->sort_column !== 'id') {
            $query->orderBy('id');
        }

        if ($user->is_admin && $this->isViewingOtherUsers($user)) {
            $query->with('user:id,name,profile_photo_path');
        }

        return $query;
    }

    private function isViewingOtherUsers(User $user): bool
    {
        $filters = $user->settings->photo_filters;

        if ($filters === null) {
            return false;
        }

        return $filters->all_users || $filters->user_ids !== [];
    }
}
