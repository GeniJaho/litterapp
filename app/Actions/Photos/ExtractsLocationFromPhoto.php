<?php

namespace App\Actions\Photos;

use Illuminate\Http\UploadedFile;

interface ExtractsLocationFromPhoto
{
    /**
     * @return array<string, float>
     */
    public function run(UploadedFile $photo): array;
}
