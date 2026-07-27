<?php

declare(strict_types=1);

namespace App\Actions\Photos;

use App\DTO\PhotoSuggestionResult;
use App\Models\Photo;

interface SuggestsPhotoTags
{
    public function run(Photo $photo): ?PhotoSuggestionResult;
}
