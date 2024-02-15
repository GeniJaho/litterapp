<?php

namespace App\Actions\Photos;

use App\DTO\PhotoFilters;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FilterPhotosAction
{
    public function run(User $user): LengthAwarePaginator
    {
        $filters = $user->settings->photo_filters ?? new PhotoFilters();

        $photos = $user
            ->photos()
            ->withExists('items')
            ->when($filters->item_ids !== [], fn ($query) => $query
                ->whereHas('items', fn ($query) => $query
                    ->whereIn('item_id', $filters->item_ids)
                )
            )
            ->when($filters->tag_ids !== [], fn ($query) => $query
                ->whereHas('items', fn ($query) => $query
                    ->join('photo_item_tag', 'photo_items.id', '=', 'photo_item_tag.photo_item_id')
                    ->whereIn('photo_item_tag.tag_id', $filters->tag_ids)
                )
            )
            ->when($filters->uploaded_from, fn ($query) => $query->where('created_at', '>=', $filters->uploaded_from))
            ->when($filters->uploaded_until, fn ($query) => $query->where('created_at', '<=', $filters->uploaded_until))
            ->when($filters->taken_from_local, fn ($query) => $query->where('taken_at_local', '>=', $filters->taken_from_local))
            ->when($filters->taken_until_local, fn ($query) => $query->where('taken_at_local', '<=', $filters->taken_until_local))
            ->when($filters->has_gps === true, fn ($query) => $query->whereNotNull('latitude')->whereNotNull('longitude'))
            ->when($filters->has_gps === false, fn ($query) => $query->where(fn ($query) => $query->whereNull('latitude')->orWhereNull('longitude')))
            ->when($filters->is_tagged === true, fn ($query) => $query->whereHas('items'))
            ->when($filters->is_tagged === false, fn ($query) => $query->whereDoesntHave('items'))
            ->latest('id')
            ->paginate(12);

        $photos->getCollection()->transform(function (Photo $photo) {
            $photo->append('full_path');

            return $photo;
        });

        return $photos;
    }
}
