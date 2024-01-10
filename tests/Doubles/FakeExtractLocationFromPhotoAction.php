<?php

namespace Tests\Doubles;

use App\Actions\Photos\ExtractsLocationFromPhoto;
use Illuminate\Http\UploadedFile;

class FakeExtractLocationFromPhotoAction implements ExtractsLocationFromPhoto
{
    public function run(UploadedFile $photo): array
    {
        return [];
    }
}
