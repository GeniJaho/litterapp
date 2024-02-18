<?php

namespace App\Actions\Photos;

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
        $photos = $user
            ->photos()
            ->filter($user->settings->photo_filters)
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