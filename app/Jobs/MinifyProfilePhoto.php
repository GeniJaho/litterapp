<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
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

        $photo = Storage::disk('public')->path($this->user->profile_photo_path);

        $image = ImageManager::gd()->read($photo);
        $image->scaleDown(height: 500);
        $image->save(quality: 50);
    }
}
