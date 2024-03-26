<?php

namespace App\Actions\Photos;

use Intervention\Image\Collection;

class ExtractLocationFromPhotoAction
{
    /**
     * @param  Collection<mixed>  $exif
     * @return float[]
     */
    public function run(Collection $exif): array
    {
        /** @var array<string, string[]> $gps */
        $gps = (array) $exif->get('GPS');

        if ($this->isExifInvalid($gps)) {
            return [];
        }

        $result = $this->convertExifToLatLng($gps);

        if ($result['latitude'] === 0.0 && $result['longitude'] === 0.0) {
            return [];
        }

        return $result;
    }

    /**
     * @param  array<string, string|string[]>  $exif
     * @return float[]
     */
    private function convertExifToLatLng(array $exif): array
    {
        $GPSLatitudeRef = $exif['GPSLatitudeRef'];
        $GPSLatitude = $exif['GPSLatitude'];
        $GPSLongitudeRef = $exif['GPSLongitudeRef'];
        $GPSLongitude = $exif['GPSLongitude'];

        $latDegrees = $this->gpsToNumeric($GPSLatitude[0] ?? null);
        $latMinutes = $this->gpsToNumeric($GPSLatitude[1] ?? null);
        $latSeconds = $this->gpsToNumeric($GPSLatitude[2] ?? null);

        $lonDegrees = $this->gpsToNumeric($GPSLongitude[0] ?? null);
        $lonMinutes = $this->gpsToNumeric($GPSLongitude[1] ?? null);
        $lonSeconds = $this->gpsToNumeric($GPSLongitude[2] ?? null);

        $latDirection = ($GPSLatitudeRef === 'W' || $GPSLatitudeRef === 'S') ? -1 : 1;
        $lonDirection = ($GPSLongitudeRef === 'W' || $GPSLongitudeRef === 'S') ? -1 : 1;

        $latitude = $latDirection * ($latDegrees + ($latMinutes / 60) + ($latSeconds / (60 * 60)));
        $longitude = $lonDirection * ($lonDegrees + ($lonMinutes / 60) + ($lonSeconds / (60 * 60)));

        return ['latitude' => $latitude, 'longitude' => $longitude];
    }

    private function gpsToNumeric(?string $coordinates): float
    {
        if ($coordinates === null || $coordinates === '' || $coordinates === '0') {
            return 0.0;
        }

        $parts = explode('/', $coordinates);

        if ($parts == []) {
            return 0.0;
        }

        if (count($parts) === 1) {
            return (float) $parts[0];
        }

        if ((float) $parts[1] === 0.0) {
            return 0.0;
        }

        return (float) $parts[0] / (float) $parts[1];
    }

    /**
     * @param array<string, string[]> $exif
     */
    private function isExifInvalid(array $exif): bool
    {
        return $exif === [] ||
            ! isset($exif['GPSLatitudeRef']) ||
            ! isset($exif['GPSLatitude']) ||
            ! isset($exif['GPSLongitudeRef']) ||
            ! isset($exif['GPSLongitude']);
    }
}
