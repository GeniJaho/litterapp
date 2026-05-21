<?php

namespace App\Jobs;

use App\Actions\Photos\MinifyPhotoAction;
use App\Models\Photo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class MinifyPhoto implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Photo $photo) {}

    public function handle(MinifyPhotoAction $minifyPhoto): void
    {
        if ($this->photo->path === '') {
            return;
        }

        $content = Storage::get($this->photo->path);

        if ($content === null) {
            return;
        }

        $minified = $minifyPhoto->run($content);

        Storage::put($this->photo->path, $minified);

        $this->photo->updateQuietly([
            'size_kb' => (int) round($minified->size() / 1024),
        ]);
    }
}
