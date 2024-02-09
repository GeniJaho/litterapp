<?php

namespace App\Actions\Photos;

use Illuminate\Support\Carbon;
use Intervention\Image\Collection;

class ExtractDateTakenFromPhotoAction
{
    /**
     * @param Collection<mixed> $exif
     */
    public function run(Collection $exif): ?Carbon
    {
        $dateTimeOriginal = $exif->get('EXIF.DateTimeOriginal');

        if ($dateTimeOriginal) {
            return Carbon::parse($dateTimeOriginal);
        }

        $dateTimeDigitized = $exif->get('EXIF.DateTimeDigitized');

        if ($dateTimeDigitized) {
            return Carbon::parse($dateTimeDigitized);
        }

        $dateTime = $exif->get('IDF0.DateTime');

        if ($dateTime) {
            return Carbon::parse($dateTime);
        }

        return null;
    }
}
