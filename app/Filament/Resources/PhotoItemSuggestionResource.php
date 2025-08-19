<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoItemSuggestionResource\Pages\ListPhotoItemSuggestions;
use App\Models\PhotoItemSuggestion;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PhotoItemSuggestionResource extends Resource
{
    protected static ?string $model = PhotoItemSuggestion::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $modelLabel = 'Item Suggestions';

    protected static ?int $navigationSort = 6;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('photo.user.name')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('photo.full_path')
                    ->label('Photo'),
                TextColumn::make('item.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('score')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_accepted')
                    ->boolean(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('user')
                    ->relationship('photo.user', 'name')
                    ->multiple()
                    ->preload(),
                SelectFilter::make('item')
                    ->relationship('item', 'name')
                    ->multiple()
                    ->preload(),
            ], layout: FiltersLayout::AboveContent)
            ->persistFiltersInSession();
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
            'index' => ListPhotoItemSuggestions::route('/'),
        ];
    }
}
