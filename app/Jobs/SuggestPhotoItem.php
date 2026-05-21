<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Photos\SuggestsPhotoTags;
use App\DTO\PhotoSuggestionResult;
use App\Models\Photo;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

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

    public function handle(SuggestsPhotoTags $action): void
    {
        $result = $action->run($this->photo);

        if (! $result instanceof PhotoSuggestionResult) {
            throw new RuntimeException("Failed to get photo suggestions for photo {$this->photo->id}");
        }

        $attributes = $result->toSuggestionAttributes();

        if ($attributes === null) {
            throw new RuntimeException("No items returned in photo suggestion result for photo {$this->photo->id}");
        }

        if ($this->photo->items()->where('item_id', $attributes['item_id'])->exists()) {
            return;
        }

        $this->photo->photoSuggestions()->create($attributes);
    }
}
