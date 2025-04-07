<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use App\Models\Item;
use App\Models\ItemType;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        /** @var Tab[] $itemTypes */
        $itemTypes = ItemType::query()->get()
            ->mapWithKeys(fn (ItemType $itemType): array => [
                $itemType->name => Tab::make($itemType->name)
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('item_type_id', $itemType->id))
                    ->badge(Item::query()->where('item_type_id', $itemType->id)->count()),
            ])
            ->toArray();

        return [
            'all' => Tab::make()->badge(Item::query()->count()),
            ...$itemTypes,
            'none' => Tab::make('No Type')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('item_type_id'))
                ->badge(Item::query()->whereNull('item_type_id')->count()),
        ];
    }
}
