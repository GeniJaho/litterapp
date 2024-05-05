<?php

namespace App\Actions\Photos;

use App\DTO\PhotoExport;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\LazyCollection;

class ExportPhotosAction
{
    /**
     * @return LazyCollection<int, PhotoExport>
     */
    public function run(User $user): LazyCollection
    {
        $photos = $user
            ->photos()
            ->filter($user->settings->photo_filters)
            ->with(['photoItems' => fn (Builder $q) => $q
                ->with('item:id,name')
                ->with('tags:id,name'),
            ])
            ->lazyById();

        return PhotoExport::collect($photos);
    }
}
