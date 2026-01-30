<?php

namespace App\Filament\Pages;

use App\Actions\TagShortcuts\CopyUserTagShortcutsAction;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * @property Form $form
 */
class CopyUserTagShortcuts extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Copy Tag Shortcuts';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.copy-user-tag-shortcuts';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ->statePath('data');
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('copy')
                ->label('Copy Tag Shortcuts')
                ->submit('copy'),
        ];
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
