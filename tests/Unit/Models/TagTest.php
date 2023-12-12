<?php

use App\Models\Tag;
use App\Models\TagType;

test('a tag has a type', function () {
    $tagType = TagType::factory()->create();
    $tag = Tag::factory()->create(['tag_type_id' => $tagType->id]);

    expect($tag->type)->toBeInstanceOf(TagType::class)
        ->and($tag->type->id)->toBe($tagType->id);
});
