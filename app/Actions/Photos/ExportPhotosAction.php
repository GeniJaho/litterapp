<?php

namespace App\Actions\Photos;

use App\DTO\PhotoExport;
use App\Models\Photo;
use App\Models\User;
use Generator;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ExportPhotosAction
{
    public function run(User $user): Generator
    {
        $filters = $user->settings->photo_filters;
        $userIds = $filters !== null ? $filters->user_ids : [];

        /** @var \Illuminate\Database\Eloquent\Builder<Photo> $query */
        $query = $user->is_admin && $userIds !== []
            ? Photo::query()->whereIn('user_id', $userIds)
            : $user->photos();

        $photos = $query
            ->filter($filters)
            ->with(['photoItems' => fn (Builder $q) => $q
                ->with('item:id,name')
                ->with('tags:id,tag_type_id,name')
                ->with('tags.type:id,name'),
            ])
            ->lazyById();

        foreach ($photos as $photo) {
            yield PhotoExport::fromModel($photo);
        }
    }
}
