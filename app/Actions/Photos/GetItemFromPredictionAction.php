<?php

namespace App\Actions\Photos;

use App\DTO\PhotoItemPrediction;
use App\Models\Item;

class GetItemFromPredictionAction
{
    /**
     * @var array<string, string>
     */
    private array $itemClassNames = [
        'aluminium-foil' => 'Aluminium Foil',
        'balloon' => 'Balloon',
        'bottle' => 'Bottle',
        'can' => 'Can',
        'cap' => 'Cap (Bottle Cap/-Lid/-Top)',
        'cigarette-butt' => 'Cigarette Butt',
        'drink-pouch' => 'Drink Pouch',
        'straw' => 'Straw',
        'cup' => 'Cup',
        'drink-carton' => 'Drink Carton',
        'air-freshener' => 'Air Freshener',
        'battery' => 'Battery',
        'bicycle-light' => 'Bicycle Light',
        'cable-tie' => 'Cable Tie',
        'crown-cap' => 'Crown Cap',
        'facemask' => 'Face Mask',
        'firework' => 'Firework',
        'glove' => 'Glove',
        'glove-industrial' => 'Industrial Glove',
        'joint-tube' => 'Joint Tube',
        'lid' => 'Lid',
        'lighter' => 'Lighter',
        'nitrous-canister' => 'Nitrous Canister',
        'packaging' => 'Packaging',
        'pull-ring' => 'Pull Ring',
        'rope' => 'Rope',
        'saucepacket' => 'Sauce Packet',
        'sleeve-label' => 'Sleeve Label',
        'stick-icelolly' => 'Stick (Icelolly)',
        'wet-wipes' => 'Wet Wipes',
        'zip-bag' => 'Zip Bag',
    ];

    public function run(PhotoItemPrediction $prediction): ?Item
    {
        if (! isset($this->itemClassNames[$prediction->class_name])) {
            logger()->error('Unknown item class name', [
                'class_name' => $prediction->class_name,
                'score' => $prediction->score,
            ]);

            return null;
        }

        $foundItem = Item::query()->where('name', $this->itemClassNames[$prediction->class_name])->first();

        if (! $foundItem instanceof Item) {
            logger()->error('Item not found', [
                'class_name' => $prediction->class_name,
                'score' => $prediction->score,
                'item_name' => $this->itemClassNames[$prediction->class_name],
            ]);
        }

        return $foundItem;
    }
}
