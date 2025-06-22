<?php

namespace App\Actions\Photos;

use App\DTO\PhotoItemPrediction;
use App\Models\Photo;

interface ClassifiesPhoto
{
    public function run(Photo $photo): ?PhotoItemPrediction;
}
