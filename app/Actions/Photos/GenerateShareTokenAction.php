<?php

namespace App\Actions\Photos;

use App\Models\Photo;
use Illuminate\Support\Str;

class GenerateShareTokenAction
{
    public function run(Photo $photo, ?int $expiresInDays = null): string
    {
        if (! $photo->share_token) {
            $photo->share_token = Str::uuid()->toString();
        }

        $photo->share_expires_at = $expiresInDays ? now()->addDays($expiresInDays) : null;
        $photo->save();

        return $photo->share_token;
    }
}
