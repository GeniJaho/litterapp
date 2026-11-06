<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(191),
                DateTimePicker::make('email_verified_at')
                    ->nullable()
                    ->displayFormat('MMMM D, YYYY'),
                Textarea::make('admin_notes')
                    ->label('Notes')
                    ->helperText('Only visible to admins.')
                    ->rows(5)
                    ->maxLength(10000)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->persistFiltersInSession()
            ->filtersTriggerAction(fn (Action $action): Action => $action->button()->label('Filters'))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                ImageColumn::make('profile_photo_path')
                    ->label('Avatar')
                    ->disk(config()->string('jetstream.profile_photo_disk'))
                    ->circular()
                    ->defaultImageUrl(fn (User $user): string => 'https://ui-avatars.com/api/?background=0D8ABC&color=fff&name='.urlencode((string) $user->name)),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('photos_count')
                    ->counts('photos')
                    ->label('Photos Uploaded')
                    ->sortable()
                    ->toggleable()
                    ->numeric(),
                TextColumn::make('tagged_photos_count')
                    ->counts(['photos as tagged_photos_count' => fn (Builder $query) => $query->whereHas('photoItems')])
                    ->label('Photos Tagged')
                    ->sortable()
                    ->toggleable()
                    ->numeric(),
                TextColumn::make('photos_without_gps_count')
                    ->counts(['photos as photos_without_gps_count' => fn (Builder $query) => $query
                        ->where(fn (Builder $query) => $query->whereNull('latitude')->orWhereNull('longitude'))])
                    ->label('Photos Without GPS')
                    ->sortable()
                    ->toggleable()
                    ->numeric(),
                TextColumn::make('currentTeam.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('admin_notes')
                    ->label('Notes')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
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
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['currentTeam', 'ownedTeams']))
            ->filters([
                SelectFilter::make('team')
                    ->relationship('currentTeam', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('impersonate')
                    ->visible(fn (User $user): bool => $user->canBeImpersonated())
                    ->icon('heroicon-o-user-circle')
                    ->url(fn (User $user): string => route('impersonate', $user)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc')
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
