<?php

namespace Tests\Doubles;

use App\Actions\Photos\ExtractsExifFromPhoto;
use Illuminate\Http\UploadedFile;

class FakeExtractExifFromPhotoAction implements ExtractsExifFromPhoto
{
    private array $exifData;

    public function __construct()
    {
        $this->exifData = [
            'latitude' => 40.05,
            'longitude' => -77.15,
            'taken_at_local' => now()->toDateTimeString(),
        ];
    }

    public function run(UploadedFile $photo): array
    {
        return $this->exifData;
    }

    public function withExif(array $exif): self
    {
        $this->exifData = $exif;

        return $this;
    }
}
