<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages\CreateAnnouncement;
use App\Filament\Resources\AnnouncementResource\Pages\EditAnnouncement;
use App\Filament\Resources\AnnouncementResource\Pages\ListAnnouncements;
use App\Models\Announcement;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(191),
                Textarea::make('body')
                    ->required()
                    ->rows(4)
                    ->maxLength(2000),
                TextInput::make('link_url')
                    ->label('Link URL')
                    ->url()
                    ->nullable()
                    ->maxLength(191),
                TextInput::make('link_label')
                    ->nullable()
                    ->maxLength(191)
                    ->helperText('Shown as the call-to-action label when a link URL is set.'),
                FileUpload::make('image_path')
                    ->label('Image')
                    ->image()
                    ->directory('announcements')
                    ->nullable(),
                DateTimePicker::make('published_at')
                    ->seconds(false)
                    ->nullable()
                    ->helperText('Times are in UTC. Leave empty to keep as a draft. Future dates schedule visibility.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->limit(60),
                IconColumn::make('image_path')
                    ->label('Image')
                    ->boolean(),
                IconColumn::make('link_url')
                    ->label('Link')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Draft'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAnnouncements::route('/'),
            'create' => CreateAnnouncement::route('/create'),
            'edit' => EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
