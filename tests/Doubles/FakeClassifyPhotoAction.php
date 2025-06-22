<?php

namespace Tests\Doubles;

use App\Actions\Photos\ClassifiesPhoto;
use App\DTO\PhotoItemPrediction;
use App\Models\Photo;

class FakeClassifyPhotoAction implements ClassifiesPhoto
{
    private ?PhotoItemPrediction $result = null;

    private bool $shouldFail = false;

    public function run(Photo $photo): ?PhotoItemPrediction
    {
        if ($this->shouldFail) {
            return null;
        }

        return $this->result ?? new PhotoItemPrediction('bottle', 0.95);
    }

    public function shouldReturnPrediction(PhotoItemPrediction $prediction): self
    {
        $this->result = $prediction;

        return $this;
    }

    public function shouldFail(): self
    {
        $this->shouldFail = true;

        return $this;
    }
}
