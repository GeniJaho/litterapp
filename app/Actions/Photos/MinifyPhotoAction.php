<?php

namespace App\Actions\Photos;

use Illuminate\Contracts\Filesystem\Filesystem;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\EncodedImageInterface;

class MinifyPhotoAction
{
    public function run(Filesystem|string $content): EncodedImageInterface
    {
        $image = ImageManager::gd()->read($content);

        return $image->encode(new AutoEncoder(quality: 50));
    }
}
