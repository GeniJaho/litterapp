<?php

namespace Tests\Doubles;

use App\Actions\Photos\ExtractsExifFromPhoto;
use Illuminate\Http\UploadedFile;

class FakeExtractExifFromPhotoAction implements ExtractsExifFromPhoto
{
    public function run(UploadedFile $photo): array
    {
        return [];
    }
}
