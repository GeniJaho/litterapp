<?php

namespace App\Filament\Pages;

use App\Models\Item;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('fromItemId')
                    ->label('Item to Replace')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => $this->searchItems($search))
                    ->getOptionLabelUsing(fn (int $value): string => $this->getItemLabel($value))
                    ->required()
                    ->helperText('Select the item that should be replaced'),
                Select::make('toItemId')
                    ->label('Replace With')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => $this->searchItems($search))
                    ->getOptionLabelUsing(fn (int $value): string => $this->getItemLabel($value))
                    ->required()
                    ->different('fromItemId')
                    ->helperText('Select the new item that should be used instead'),
            ])
            ->statePath('data');
    }

    protected function getItemLabel(int $value): string
    {
        $item = Item::query()->withCount('photoItems')->find($value);

        return $item ? "{$item->name} ({$item->photo_items_count}x)" : '';
    }

    /**
     * @return array<int, string>
     */
    protected function searchItems(string $search): array
    {
        /** @var array<int, string> */
        return Item::query()
            ->whereLike('name', "%{$search}%")
            ->withCount('photoItems')
            ->limit(50)
            ->get()
            ->mapWithKeys(fn (Item $item): array => [
                $item->id => "{$item->name} ({$item->photo_items_count}x)",
            ])
            ->toArray();
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

        if ($data['fromItemId'] === $data['toItemId']) {
            Notification::make()
                ->title('Same item selected')
                ->body('The item to replace and the replacement item cannot be the same.')
                ->danger()
                ->send();

            return;
        }

        /** @var Item $fromItem */
        $fromItem = Item::findOrFail($data['fromItemId']);
        /** @var Item $toItem */
        $toItem = Item::findOrFail($data['toItemId']);

        $totalFromPhotos = DB::table('photo_items')
            ->where('item_id', $fromItem->id)
            ->count();

        if ($totalFromPhotos === 0) {
            Notification::make()
                ->title('Nothing to replace')
                ->body("'{$fromItem->name}' is not used in any photos.")
                ->warning()
                ->send();

            return;
        }

        $photoIdsWithTarget = DB::table('photo_items')
            ->where('item_id', $toItem->id)
            ->pluck('photo_id');

        $affectedPhotos = DB::table('photo_items')
            ->where('item_id', $fromItem->id)
            ->whereNotIn('photo_id', $photoIdsWithTarget)
            ->distinct()
            ->count('photo_id');

        $skippedPhotos = DB::table('photo_items')
            ->where('item_id', $fromItem->id)
            ->whereIn('photo_id', $photoIdsWithTarget)
            ->distinct()
            ->count('photo_id');

        $this->replacingFromId = $fromItem->id;
        $this->replacingToId = $toItem->id;
        $this->affectedPhotos = $affectedPhotos;
        $this->skippedPhotos = $skippedPhotos;

        $this->mountAction('confirmReplace');
    }

    public ?int $replacingFromId = null;

    public ?int $replacingToId = null;

    public int $affectedPhotos = 0;

    public int $skippedPhotos = 0;

    public function confirmReplaceAction(): Action
    {
        return Action::make('confirmReplace')
            ->requiresConfirmation()
            ->modalHeading('Confirm Item Replacement')
            ->modalDescription(function (): string {
                /** @var Item|null $from */
                $from = Item::find($this->replacingFromId);
                /** @var Item|null $to */
                $to = Item::find($this->replacingToId);

                if (! $from || ! $to) {
                    return 'Please select both items first.';
                }

                $message = "This will replace '{$from->name}' with '{$to->name}' in {$this->affectedPhotos} photo(s).";

                if ($this->skippedPhotos > 0) {
                    $message .= " {$this->skippedPhotos} photo(s) already have '{$to->name}' and will be skipped (the '{$from->name}' entry will be removed).";
                }

                return $message;
            })
            ->action(function (): void {
                /** @var Item|null $fromItem */
                $fromItem = Item::find($this->replacingFromId);
                /** @var Item|null $toItem */
                $toItem = Item::find($this->replacingToId);

                if (! $fromItem || ! $toItem) {
                    return;
                }

                $photoIdsWithTarget = DB::table('photo_items')
                    ->where('item_id', $toItem->id)
                    ->pluck('photo_id');

                $deletedRows = DB::table('photo_items')
                    ->where('item_id', $fromItem->id)
                    ->whereIn('photo_id', $photoIdsWithTarget)
                    ->delete();

                $replacedRows = DB::table('photo_items')
                    ->where('item_id', $fromItem->id)
                    ->update(['item_id' => $toItem->id]);

                Notification::make()
                    ->title('Items Replaced')
                    ->body(
                        "Replaced '{$fromItem->name}' with '{$toItem->name}' in {$replacedRows} photo(s)."
                        .($deletedRows > 0 ? " Skipped {$deletedRows} (already had '{$toItem->name}')." : '')
                    )
                    ->success()
                    ->send();

                $this->replacingFromId = null;
                $this->replacingToId = null;
                $this->form->fill();
            });
    }
}
