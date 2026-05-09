<?php

namespace Tests\Doubles;

use App\Actions\Photos\SuggestsPhotoTags;
use App\DTO\PhotoSuggestionResult;
use App\Models\Photo;

class FakeSuggestPhotoTagsAction implements SuggestsPhotoTags
{
    private ?PhotoSuggestionResult $result = null;

    private bool $shouldFail = false;

    public function run(Photo $photo): ?PhotoSuggestionResult
    {
        if ($this->shouldFail) {
            return null;
        }

        return $this->result ?? new PhotoSuggestionResult(
            items: [['id' => 1, 'name' => 'Bottle', 'confidence' => 0.95, 'count' => 10]],
            brands: [],
            content: [],
        );
    }

    public function shouldReturnResult(PhotoSuggestionResult $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function shouldFail(): self
    {
        $this->shouldFail = true;

        return $this;
    }
}
