<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoResource\Pages\ListPhotos;
use App\Models\Photo;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
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
            ->filtersTriggerAction(fn (Action $action): Action => $action->button()->label('Filters'))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('full_path')
                    ->label('Photo')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('path', 'like', "%{$search}%")),
                TextColumn::make('gps')
                    ->label('GPS')
                    ->getStateUsing(fn (Photo $photo): ?string => $photo->latitude && $photo->longitude
                        ? "{$photo->latitude}, {$photo->longitude}"
                        : null
                    )
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->where('latitude', 'like', "%{$search}%")
                        ->orWhere('longitude', 'like', "%{$search}%")
                    ),
                TextColumn::make('original_file_name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('taken_at_local')
                    ->dateTime()
                    ->label('Date taken (Local time)')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Uploaded at')
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
                        blank: fn (Builder $query): Builder => $query
                    ),
                Filter::make('taken_at_local')
                    ->form([
                        DatePicker::make('local_date_taken_from')->label('Date taken from (Local time)'),
                        DatePicker::make('local_date_taken_until')->label('Date taken until (Local time)'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['local_date_taken_from'],
                            fn (Builder $query, string $date): Builder => $query->whereDate('taken_at_local', '>=', $date),
                        )
                        ->when(
                            $data['local_date_taken_until'],
                            fn (Builder $query, string $date): Builder => $query->whereDate('taken_at_local', '<=', $date),
                        )),
                Filter::make('created_at')
                    ->form([
                        DateTimePicker::make('created_at_from')->label('Uploaded from'),
                        DateTimePicker::make('created_at_until')->label('Uploaded until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['created_at_from'],
                            fn (Builder $query, string $date): Builder => $query->where('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_at_until'],
                            fn (Builder $query, string $date): Builder => $query->where('created_at', '<=', $date),
                        )),
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
