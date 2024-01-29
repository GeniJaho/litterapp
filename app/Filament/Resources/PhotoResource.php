<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoResource\Pages\ListPhotos;
use App\Models\Photo;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PhotoResource extends Resource
{
    protected static ?string $model = Photo::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    public static function table(Table $table): Table
    {
        return $table
            ->persistFiltersInSession()
            ->filtersTriggerAction(fn ($action) => $action->button()->label('Filters'))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('path')
                    ->label('Photo')
                    ->searchable(),
                TextColumn::make('gps')
                    ->label('GPS')
                    ->getStateUsing(fn (Photo $photo): ?string => $photo->latitude && $photo->longitude
                        ? "{$photo->latitude}, {$photo->longitude}"
                        : null
                    )
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->where('latitude', 'like', "%{$search}%")
                            ->orWhere('longitude', 'like', "%{$search}%");
                    }),
                TextColumn::make('original_file_name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('has_gps')
                    ->label('Has GPS')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('latitude')->whereNotNull('longitude'),
                        false: fn (Builder $query) => $query->whereNull('latitude')->orWhereNull('longitude'),
                        blank: fn (Builder $query) => $query
                    )
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
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
            'index' => ListPhotos::route('/'),
        ];
    }
}
