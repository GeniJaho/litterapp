<?php

namespace App\Filament\Resources\PhotoResource\Pages;

use App\Filament\Resources\PhotoResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPhotos extends ListRecords
{
    protected static string $resource = PhotoResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Photos'),
            'tagged' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->whereHas('items')),
            'untagged' => Tab::make()->modifyQueryUsing(fn (Builder $query) => $query->whereDoesntHave('items')),
        ];
    }
}
