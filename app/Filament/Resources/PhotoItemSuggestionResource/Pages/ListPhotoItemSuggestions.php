<?php

declare(strict_types=1);

namespace App\Filament\Resources\PhotoItemSuggestionResource\Pages;

use App\Filament\Resources\PhotoItemSuggestionResource;
use Filament\Resources\Pages\ListRecords;

class ListPhotoItemSuggestions extends ListRecords
{
    protected static string $resource = PhotoItemSuggestionResource::class;
}
