<?php

namespace App\Jobs;

use App\Models\Photo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\ImageManager;

class MinifyPhoto implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly Photo $photo) {}

    public function handle(): void
    {
        if ($this->photo->path === '') {
            return;
        }

        $content = Storage::get($this->photo->path);

        if ($content === null) {
            return;
        }

        $image = ImageManager::gd()->read($content);

        $minified = $image->encode(new AutoEncoder(quality: 50));

        Storage::put($this->photo->path, $minified);

        $this->photo->update([
            'size_kb' => (int) round($minified->size() / 1024),
        ]);
    }
}
