<?php

namespace App\Actions\Photos;

use Illuminate\Support\Carbon;
use Intervention\Image\Collection;

class ExtractDateTakenFromPhotoAction
{
    /**
     * @param  Collection<mixed>  $exif
     */
    public function run(Collection $exif): ?Carbon
    {
        /** @var string|null $dateTimeOriginal */
        $dateTimeOriginal = $exif->get('EXIF.DateTimeOriginal');

        if ($dateTimeOriginal !== null) {
            return Carbon::parse($dateTimeOriginal);
        }

        /** @var string|null $dateTimeDigitized */
        $dateTimeDigitized = $exif->get('EXIF.DateTimeDigitized');

        if ($dateTimeDigitized !== null) {
            return Carbon::parse($dateTimeDigitized);
        }

        /** @var string|null $dateTime */
        $dateTime = $exif->get('IDF0.DateTime');

        if ($dateTime !== null) {
            return Carbon::parse($dateTime);
        }

        return null;
    }
}
