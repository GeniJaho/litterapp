<?php

namespace App\Actions\Photos;

use App\Models\Photo;
use Illuminate\Support\Str;

class GenerateShareTokenAction
{
    public function run(Photo $photo): string
    {
        $token = Str::uuid()->toString();

        $photo->share_token = $token;
        $photo->save();

        return $token;
    }
}
