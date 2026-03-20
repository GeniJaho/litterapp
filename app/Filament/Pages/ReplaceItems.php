<?php

namespace App\Filament\Pages;

use App\Models\Item;
use Illuminate\Support\Facades\DB;
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
class ReplaceItems extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Replace Items';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 101;

    protected static string $view = 'filament.pages.replace-items';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getItemOptions(): array
    {
        return Item::query()
            ->withCount('photoItems')
            ->get()
            ->mapWithKeys(fn (Item $item): array => [
                $item->id => "{$item->name} ({$item->photo_items_count}x)",
            ])
            ->toArray();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('fromItemId')
                    ->label('Item to Replace')
                    ->options(fn () => $this->getItemOptions())
                    ->searchable()
                    ->required()
                    ->helperText('Select the item that should be replaced'),
                Select::make('toItemId')
                    ->label('Replace With')
                    ->options(fn () => $this->getItemOptions())
                    ->searchable()
                    ->required()
                    ->different('fromItemId')
                    ->helperText('Select the new item that should be used instead'),
            ])
            ->statePath('data');
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('replace')
                ->label('Replace Items')
                ->submit('replace'),
        ];
    }

    public function replace(): void
    {
        /** @var array{fromItemId: int, toItemId: int} $data */
        $data = $this->form->getState();

        /** @var Item $fromItem */
        $fromItem = Item::findOrFail($data['fromItemId']);
        /** @var Item $toItem */
        $toItem = Item::findOrFail($data['toItemId']);

        $affectedRows = DB::table('photo_items')
            ->where('item_id', $fromItem->id)
            ->update(['item_id' => $toItem->id]);

        Notification::make()
            ->title('Items Replaced')
            ->body("Replaced '{$fromItem->name}' with '{$toItem->name}' in {$affectedRows} photo(s)")
            ->success()
            ->send();

        $this->form->fill();
    }
}