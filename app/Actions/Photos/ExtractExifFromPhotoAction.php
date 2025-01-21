<?php

namespace App\Actions\Photos;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Collection;
use Intervention\Image\ImageManager;

class ExtractExifFromPhotoAction implements ExtractsExifFromPhoto
{
    public function __construct(
        private readonly ExtractLocationFromPhotoAction $extractLocation,
        private readonly ExtractDateTakenFromPhotoAction $extractDateTaken,
    ) {}

    public function run(UploadedFile $photo): array
    {
        $image = ImageManager::gd()->read($photo);

        /** @var Collection<mixed> $exif */
        $exif = $image->exif();

        $location = $this->extractLocation->run($exif);

        return [
            'latitude' => $location['latitude'] ?? null,
            'longitude' => $location['longitude'] ?? null,
            'taken_at_local' => $this->extractDateTaken->run($exif),
        ];
    }
}
