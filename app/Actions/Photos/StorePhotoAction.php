<?php

namespace App\Actions\Photos;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Interfaces\EncodedImageInterface;

readonly class StorePhotoAction
{
    public function __construct(private MinifyPhotoAction $minifyPhoto) {}

    /**
     * @param  array<string, mixed>  $exif
     */
    public function run(UploadedFile $photo, User $user, array $exif, string $contentHash): Photo
    {
        $photoSize = $this->getFileSizeInKb($photo);

        if ($photoSize > 300) {
            $minified = $this->minifyPhoto->run($photo->getContent());

            $path = "photos/{$photo->hashName()}";

            Storage::put($path, $minified->toString());

            $photoSize = $this->getFileSizeInKb($minified);
        } else {
            $path = $photo->store('photos');
        }

        return DB::transaction(function () use ($user, $path, $photo, $photoSize, $exif, $contentHash): Photo {
            $createdPhoto = Photo::create([
                'user_id' => $user->id,
                'path' => $path,
                'original_file_name' => $photo->getClientOriginalName(),
                'size_kb' => $photoSize,
                'latitude' => $exif['latitude'] ?? null,
                'longitude' => $exif['longitude'] ?? null,
                'taken_at_local' => $exif['taken_at_local'] ?? null,
            ]);

            $createdPhoto->details()->create([
                'user_id' => $user->id,
                'content_hash' => $contentHash,
            ]);

            return $createdPhoto;
        });
    }

    private function getFileSizeInKb(UploadedFile|EncodedImageInterface $photo): ?int
    {
        $size = $photo instanceof UploadedFile
            ? $photo->getSize()
            : $photo->size();

        if ($size === false) {
            return null;
        }

        return (int) round($size / 1024);
    }
}
