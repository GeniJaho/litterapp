<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\ImageManager;

class MinifyProfilePhoto implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly User $user)
    {
    }

    public function handle(): void
    {
        if ($this->user->profile_photo_path === null) {
            return;
        }

        $photo = Storage::get($this->user->profile_photo_path);

        $image = ImageManager::gd()->read($photo);
        $image->scaleDown(height: 500);

        $minified = $image->encode(new AutoEncoder(quality: 50));

        Storage::put($this->user->profile_photo_path, $minified);
    }
}
