<?php

namespace App\Actions\Photos;

use Illuminate\Http\UploadedFile;

interface ExtractsLocationFromPhoto
{
    public function run(UploadedFile $photo): array;
}
