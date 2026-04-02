<?php

namespace App\Actions\Photos;

use App\Models\Photo;
use Illuminate\Support\Str;

class GenerateShareTokenAction
{
    public function run(Photo $photo): string
    {
        $photo->share_token = Str::uuid()->toString();
        $photo->save();

        return $photo->share_token;
    }
}
