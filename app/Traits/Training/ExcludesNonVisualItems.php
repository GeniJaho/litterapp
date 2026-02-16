<?php

namespace App\Traits\Training;

use App\Models\Item;
use App\Models\Tag;
use App\Models\TagType;
use Illuminate\Support\Collection;

trait ExcludesNonVisualItems
{
    /**
     * @return list<string>
     */
    protected function excludedItemNames(): array
    {
        return [
            'Piece of <add material>',
            'Unidentified/Unknown/Other',
            'OTHER (Please add this missing item to the picklist)',
            'Polluted Area',
            'DepositItemInBin',
            'Poo',
            'Poo (Dog)',
            'Poo (Cat)',
            'Animal (dead)',
        ];
    }

    /**
     * @return Collection<int|string, mixed>
     */
    protected function getExcludedItemIds(): Collection
    {
        return Item::query()
            ->whereIn('name', $this->excludedItemNames())
            ->pluck('id');
    }

    /**
     * Get tag IDs that should be excluded (placeholder "OTHER" tags for brand and content).
     *
     * @return Collection<int|string, mixed>
     */
    protected function getExcludedTagIds(): Collection
    {
        $tagTypeIds = TagType::query()
            ->whereIn('slug', ['brand', 'content'])
            ->pluck('id');

        return Tag::query()
            ->whereLike('name', 'OTHER (Please add this missing % to the picklist)')
            ->whereIn('tag_type_id', $tagTypeIds)
            ->pluck('id');
    }
}
