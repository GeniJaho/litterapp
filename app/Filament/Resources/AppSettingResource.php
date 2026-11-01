<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\AppSettingResource\Pages\ManageAppSettings;
use App\Models\AppSetting;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class AppSettingResource extends Resource
{
    protected static ?string $model = AppSetting::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Application Settings';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->required()
                    ->maxLength(191)
                    ->unique(ignoreRecord: true)
                    ->readOnly(fn (?AppSetting $record): bool => $record instanceof AppSetting),
                TextInput::make('value')
                    ->maxLength(191)
                    ->required(),
                Textarea::make('description')
                    ->nullable()
                    ->maxLength(191),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->searchable(),
                TextColumn::make('value')
                    ->searchable(),
                TextColumn::make('description')
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAppSettings::route('/'),
        ];
    }
}
