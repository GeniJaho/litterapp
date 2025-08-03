<?php

namespace App\Jobs;

use App\Actions\Photos\ClassifiesPhoto;
use App\Actions\Photos\GetItemFromPredictionAction;
use App\DTO\PhotoItemPrediction;
use App\Models\Item;
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

    public function __construct(public readonly Photo $photo) {}

    public function handle(
        ClassifiesPhoto $classifyPhotoAction,
        GetItemFromPredictionAction $getItemFromPredictionAction,
    ): int {
        $prediction = $classifyPhotoAction->run($this->photo);

        if (! $prediction instanceof PhotoItemPrediction) {
            return 1;
        }

        $item = $getItemFromPredictionAction->run($prediction);

        if (! $item instanceof Item) {
            return 1;
        }

        if ($this->photo->items()->where('item_id', $item->id)->doesntExist()) {
            $this->photo->photoItemSuggestions()->create([
                'item_id' => $item->id,
                'score' => $prediction->score,
            ]);
        }

        return 0;
    }
}
