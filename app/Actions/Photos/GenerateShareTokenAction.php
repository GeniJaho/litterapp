<?php

namespace App\Actions\Photos;

use App\Models\Photo;
use Illuminate\Support\Str;

class GenerateShareTokenAction
{
    public function run(Photo $photo, ?int $expiresInDays = null): string
    {
        $token = Str::uuid()->toString();

        $photo->share_token = $token;
        $photo->share_expires_at = $expiresInDays ? now()->addDays($expiresInDays) : null;
        $photo->save();

        return $token;
    }
}
