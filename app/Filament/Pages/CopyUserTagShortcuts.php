<?php

namespace App\Filament\Pages;

use App\Actions\TagShortcuts\CopyUserTagShortcutsAction;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class CopyUserTagShortcuts extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Copy Tag Shortcuts';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.copy-user-tag-shortcuts';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Select::make('fromUserId')
                        ->label('From User')
                        ->options(User::pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Select::make('toUserId')
                        ->label('To User')
                        ->options(User::pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->different('fromUserId'),
                ])
                    ->livewireSubmitHandler('copy')
                    ->footer([
                        Actions::make([
                            Action::make('copy')
                                ->label('Copy Tag Shortcuts')
                                ->submit('copy'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function copy(): void
    {
        /** @var array{fromUserId: int, toUserId: int} $data */
        $data = $this->form->getState();

        /** @var User $fromUser */
        $fromUser = User::findOrFail($data['fromUserId']);
        /** @var User $toUser */
        $toUser = User::findOrFail($data['toUserId']);

        $action = app(CopyUserTagShortcutsAction::class);
        $result = $action->run($fromUser, $toUser);

        Notification::make()
            ->title('Tag Shortcuts Copied')
            ->body("Copied {$result['copied']} tag shortcuts, skipped {$result['skipped']} (already existed)")
            ->success()
            ->send();

        $this->form->fill();
    }
}
