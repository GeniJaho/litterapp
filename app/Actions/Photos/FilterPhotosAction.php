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
        $filters = $user->settings->photo_filters;

        $photos = Photo::query()
            ->when(
                $user->is_admin && ! empty($filters?->user_ids),
                fn (Builder $query) => $query->whereIn('user_id', $filters->user_ids),
                fn (Builder $query) => $query->where('user_id', $user->id)
            )
            ->withExists([
                'items',
                'photoItemSuggestions' => fn (Builder $query) => $query->whereNull('is_accepted'),
            ])
            ->orderBy($user->settings->sort_column, $user->settings->sort_direction);

        if ($user->settings->sort_column !== 'id') {
            $photos->orderBy('id');
        }

        $photos = $photos
            ->paginate($user->settings->getValidPerPage())
            ->withQueryString();

        $photos->getCollection()->transform(function (Photo $photo): Photo {
            $photo->append('full_path');

            return $photo;
        });

        return $photos;
    }
}
