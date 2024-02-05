<?php

namespace App\Actions\Photos;

use Illuminate\Http\UploadedFile;

interface ExtractsExifFromPhoto
{
    /**
     * @return array<string, float>
     */
    public function run(UploadedFile $photo): array;
}
