<?php

namespace App\Actions\Photos;

use App\Models\Item;
use App\Models\Tag;
use App\Models\TagType;
use Illuminate\Database\Eloquent\Collection;

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
            ->mapWithKeys(function (Collection $values, int $key) use ($tagTypes): array {
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
