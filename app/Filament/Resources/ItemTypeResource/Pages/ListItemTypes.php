<?php

declare(strict_types=1);

namespace App\Filament\Resources\ItemTypeResource\Pages;

use App\Actions\Photos\GetTagsAndItemsAction;
use App\Filament\Resources\ItemTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Cache;

class ListItemTypes extends ListRecords
{
    protected static string $resource = ItemTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->after(fn () => Cache::forget(GetTagsAndItemsAction::CACHE_KEY)),
        ];
    }
}
