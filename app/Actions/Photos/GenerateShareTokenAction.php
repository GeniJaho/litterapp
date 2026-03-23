<?php

namespace App\Actions\Photos;

use App\Models\Photo;

class GenerateShareTokenAction
{
    public function run(Photo $photo): string
    {
        return $photo->generateShareToken();
    }
}
