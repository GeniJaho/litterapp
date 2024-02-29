<?php

namespace App\Actions\Photos;

use App\Models\Item;
use App\Models\Tag;
use App\Models\TagType;

class GetTagsAndItemsAction
{
    /**
     * @return array<string, mixed>
     */
    public function run(): array
    {
        $tagTypes = TagType::query()->get();

        $tags = Tag::query()
            ->orderBy('name')
            ->get()
            ->groupBy('tag_type_id')
            ->mapWithKeys(function ($values, $key) use ($tagTypes) {
                /** @var TagType $tagType */
                $tagType = $tagTypes->find($key);

                return [$tagType->slug => $values];
            });

        return [
            'tags' => $tags,
            'items' => Item::query()->orderBy('name')->get(),
        ];
    }
}
