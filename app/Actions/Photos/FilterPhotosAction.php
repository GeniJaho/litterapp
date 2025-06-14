<?php

namespace App\Actions\Photos;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FilterPhotosAction
{
    /**
     * @return LengthAwarePaginator<int, Photo>
     */
    public function run(User $user): LengthAwarePaginator
    {
        $photos = $user
            ->photos()
            ->filter($user->settings->photo_filters)
            ->withExists([
                'items',
                'photoItemSuggestions' => fn ($query) => $query->whereNull('is_accepted'),
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
