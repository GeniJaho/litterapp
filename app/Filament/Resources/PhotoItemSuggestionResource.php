<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoItemSuggestionResource\Pages\ListPhotoItemSuggestions;
use App\Models\PhotoItemSuggestion;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                Filter::make('score')
                    ->form([
                        TextInput::make('min_score')
                            ->label('Minimum score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->placeholder('e.g. 80'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['min_score'],
                            fn (Builder $query, string $score): Builder => $query->where('score', '>=', $score),
                        )),
                TernaryFilter::make('is_accepted')
                    ->label('Status')
                    ->placeholder('All')
                    ->trueLabel('Accepted')
                    ->falseLabel('Rejected')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->where('is_accepted', true),
                        false: fn (Builder $query): Builder => $query->where('is_accepted', false),
                        blank: fn (Builder $query): Builder => $query,
                    ),
                TernaryFilter::make('pending')
                    ->label('Pending')
                    ->placeholder('All')
                    ->trueLabel('Only pending')
                    ->falseLabel('Only reviewed')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNull('is_accepted'),
                        false: fn (Builder $query): Builder => $query->whereNotNull('is_accepted'),
                        blank: fn (Builder $query): Builder => $query,
                    ),
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
