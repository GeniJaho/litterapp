<?php

namespace App\Actions\Photos;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ExtractLocationFromPhotoAction implements ExtractsLocationFromPhoto
{
    public function run(UploadedFile $photo): array
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($photo);
        $all = $image->exif('GPS');

        if (! $all) {
            return [];
        }

        return $this->convertArrayToLatLng($all);
    }

    private function convertArrayToLatLng($gpsData)
    {
        $GPSLatitudeRef = $gpsData['GPSLatitudeRef'];
        $GPSLatitude = $gpsData['GPSLatitude'];
        $GPSLongitudeRef = $gpsData['GPSLongitudeRef'];
        $GPSLongitude = $gpsData['GPSLongitude'];

        $lat_degrees = $this->gps2Num($GPSLatitude[0] ?? null);
        $lat_minutes = $this->gps2Num($GPSLatitude[1] ?? null);
        $lat_seconds = $this->gps2Num($GPSLatitude[2] ?? null);

        $lon_degrees = $this->gps2Num($GPSLongitude[0] ?? null);
        $lon_minutes = $this->gps2Num($GPSLongitude[1] ?? null);
        $lon_seconds = $this->gps2Num($GPSLongitude[2] ?? null);

        $lat_direction = ($GPSLatitudeRef == 'W' || $GPSLatitudeRef == 'S') ? -1 : 1;
        $lon_direction = ($GPSLongitudeRef == 'W' || $GPSLongitudeRef == 'S') ? -1 : 1;

        $latitude = $lat_direction * ($lat_degrees + ($lat_minutes / 60) + ($lat_seconds / (60 * 60)));
        $longitude = $lon_direction * ($lon_degrees + ($lon_minutes / 60) + ($lon_seconds / (60 * 60)));

        return ['latitude' => $latitude, 'longitude' => $longitude];
    }

    private function gps2Num($coordPart)
    {
        if (! $coordPart) {
            return 0;
        }

        $parts = explode('/', $coordPart);
        if (count($parts) <= 0) {
            return 0;
        }

        if (count($parts) == 1) {
            return $parts[0];
        }

        return (float) $parts[0] / (float) $parts[1];
    }
}
