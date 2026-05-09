<?php

namespace App\Jobs;

use App\Actions\Photos\SuggestsPhotoTags;
use App\DTO\PhotoSuggestionResult;
use App\Models\Photo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SuggestPhotoItem implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 1800, 3600];

    public function __construct(public readonly Photo $photo) {}

    public function handle(SuggestsPhotoTags $action): int
    {
        $result = $action->run($this->photo);

        if (! $result instanceof PhotoSuggestionResult) {
            return 1;
        }

        $attributes = $result->toSuggestionAttributes();

        if ($attributes === null) {
            return 1;
        }

        if ($this->photo->items()->where('item_id', $attributes['item_id'])->exists()) {
            return 0;
        }

        $this->photo->photoSuggestions()->create($attributes);

        return 0;
    }
}
