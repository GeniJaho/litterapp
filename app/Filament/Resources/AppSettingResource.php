<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppSettingResource\Pages\ManageAppSettings;
use App\Models\AppSetting;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppSettingResource extends Resource
{
    protected static ?string $model = AppSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Application Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ->actions([
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
