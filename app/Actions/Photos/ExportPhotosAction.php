<?php

namespace App\Actions\Photos;

use App\DTO\PhotoExport;
use App\Models\User;
use Generator;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ExportPhotosAction
{
    public function run(User $user): Generator
    {
        $photos = $user
            ->photos()
            ->filter($user->settings->photo_filters)
            ->with(['photoItems' => fn (Builder $q) => $q
                ->with('item:id,name')
                ->with('tags:id,name'),
            ])
            ->lazyById();

        foreach ($photos as $photo) {
            yield PhotoExport::fromModel($photo);
        }
    }
}
