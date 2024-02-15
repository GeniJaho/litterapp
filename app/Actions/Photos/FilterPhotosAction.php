<?php

namespace App\Actions\Photos;

use App\DTO\PhotoFilters;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FilterPhotosAction
{
    /**
     * @return LengthAwarePaginator<Photo>
     */
    public function run(User $user): LengthAwarePaginator
    {
        $filters = $user->settings->photo_filters ?? new PhotoFilters();

        $photos = $user
            ->photos()
            ->filter($filters)
            ->withExists('items')
            ->latest('id')
            ->paginate(12);

        $photos->getCollection()->transform(function (Photo $photo) {
            $photo->append('full_path');

            return $photo;
        });

        return $photos;
    }
}
