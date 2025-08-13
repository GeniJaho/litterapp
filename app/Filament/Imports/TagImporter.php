<?php

namespace App\Filament\Imports;

use App\Models\Tag;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class TagImporter extends Importer
{
    protected static ?string $model = Tag::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('tag_type_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer', 'exists:tag_types,id']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:191', 'unique:tags,name']),
        ];
    }

    public function resolveRecord(): ?Tag
    {
        return new Tag;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your tag import has completed and '.number_format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if (($failedRowsCount = $import->getFailedRowsCount()) !== 0) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
