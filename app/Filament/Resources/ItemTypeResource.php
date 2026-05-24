<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Actions\Photos\GetTagsAndItemsAction;
use App\Filament\Resources\ItemTypeResource\Pages\ListItemTypes;
use App\Models\ItemType;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class ItemTypeResource extends Resource
{
    protected static ?string $model = ItemType::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(191),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Total Items')
                    ->sortable()
                    ->toggleable()
                    ->numeric(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()->after(fn () => Cache::forget(GetTagsAndItemsAction::CACHE_KEY)),
                DeleteAction::make()->after(fn () => Cache::forget(GetTagsAndItemsAction::CACHE_KEY)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->after(fn () => Cache::forget(GetTagsAndItemsAction::CACHE_KEY)),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItemTypes::route('/'),
        ];
    }
}
