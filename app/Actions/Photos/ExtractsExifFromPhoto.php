<?php

declare(strict_types=1);

namespace App\Actions\Photos;

use Illuminate\Http\UploadedFile;

interface ExtractsExifFromPhoto
{
    /**
     * @return array<string, mixed>
     */
    public function run(UploadedFile $photo): array;
}
