<?php

namespace App\Actions\Photos;

use App\Models\Photo;
use App\Models\User;

class FilterPhotosAction
{
    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function run(User $user, array $filters, ?bool $hasGPS, ?bool $isTagged): array
    {
        $filterItemIds = $filters['item_ids'] ?? [];
        $filterTagIds = array_map(fn ($id) => (int) $id, $filters['tag_ids'] ?? []);
        $uploadedFrom = $filters['uploaded_from'] ?? null;
        $uploadedUntil = $filters['uploaded_until'] ?? null;
        $takenFromLocal = $filters['taken_from_local'] ?? null;
        $takenUntilLocal = $filters['taken_until_local'] ?? null;

        $photos = $user
            ->photos()
            ->withExists('items')
            ->when($filterItemIds !== [], fn ($query) => $query
                ->whereHas('items', fn ($query) => $query
                    ->whereIn('item_id', $filterItemIds)
                )
            )
            ->when($filterTagIds !== [], fn ($query) => $query
                ->whereHas('items', fn ($query) => $query
                    ->join('photo_item_tag', 'photo_items.id', '=', 'photo_item_tag.photo_item_id')
                    ->whereIn('photo_item_tag.tag_id', $filterTagIds)
                )
            )
            ->when($uploadedFrom, fn ($query) => $query->where('created_at', '>=', $uploadedFrom))
            ->when($uploadedUntil, fn ($query) => $query->where('created_at', '<=', $uploadedUntil))
            ->when($takenFromLocal, fn ($query) => $query->whereDate('taken_at_local', '>=', $takenFromLocal))
            ->when($takenUntilLocal, fn ($query) => $query->whereDate('taken_at_local', '<=', $takenUntilLocal))
            ->when($hasGPS === true, fn ($query) => $query->whereNotNull('latitude')->whereNotNull('longitude'))
            ->when($hasGPS === false, fn ($query) => $query->where(fn ($query) => $query->whereNull('latitude')->orWhereNull('longitude')))
            ->when($isTagged === true, fn ($query) => $query->whereHas('items'))
            ->when($isTagged === false, fn ($query) => $query->whereDoesntHave('items'))
            ->latest('id')
            ->paginate(12);

        $photos->getCollection()->transform(function (Photo $photo) {
            $photo->append('full_path');

            return $photo;
        });

        return [
            'photos' => $photos,
            'filters' => [
                'item_ids' => $filterItemIds,
                'tag_ids' => $filterTagIds,
                'uploaded_from' => $uploadedFrom,
                'uploaded_until' => $uploadedUntil,
                'taken_from_local' => $takenFromLocal,
                'taken_until_local' => $takenUntilLocal,
                'has_gps' => $hasGPS !== null ? (int) $hasGPS : null,
                'is_tagged' => $isTagged !== null ? (int) $isTagged : null,
            ],
        ];
    }
}
