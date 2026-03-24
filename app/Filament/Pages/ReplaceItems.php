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
                ->requiresConfirmation()
                ->modalHeading('Confirm Item Replacement')
                ->modalDescription(function (): string {
                    $data = $this->form->getState();

                    /** @var Item|null $fromItem */
                    $fromItem = Item::find($data['fromItemId']);
                    /** @var Item|null $toItem */
                    $toItem = Item::find($data['toItemId']);

                    if (! $fromItem || ! $toItem) {
                        return 'Please select both items first.';
                    }

                    $affectedPhotos = DB::table('photo_items')
                        ->where('item_id', $fromItem->id)
                        ->whereNotIn('photo_id', function ($query) use ($toItem): void {
                            $query->select('photo_id')
                                ->from('photo_items')
                                ->where('item_id', $toItem->id);
                        })
                        ->distinct('photo_id')
                        ->count('photo_id');

                    $skippedPhotos = DB::table('photo_items')
                        ->where('item_id', $fromItem->id)
                        ->whereIn('photo_id', function ($query) use ($toItem): void {
                            $query->select('photo_id')
                                ->from('photo_items')
                                ->where('item_id', $toItem->id);
                        })
                        ->distinct('photo_id')
                        ->count('photo_id');

                    $message = "This will replace '{$fromItem->name}' with '{$toItem->name}' in {$affectedPhotos} photo(s).";

                    if ($skippedPhotos > 0) {
                        $message .= " {$skippedPhotos} photo(s) already have '{$toItem->name}' and will be skipped (the '{$fromItem->name}' entry will be removed).";
                    }

                    return $message;
                })
                ->action('replace'),
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

        // Delete photo_items where the photo already has the target item (avoid duplicates)
        $deletedRows = DB::table('photo_items')
            ->where('item_id', $fromItem->id)
            ->whereIn('photo_id', function ($query) use ($toItem): void {
                $query->select('photo_id')
                    ->from('photo_items')
                    ->where('item_id', $toItem->id);
            })
            ->delete();

        // Replace remaining photo_items from old item to new item
        $replacedRows = DB::table('photo_items')
            ->where('item_id', $fromItem->id)
            ->update(['item_id' => $toItem->id]);

        Notification::make()
            ->title('Items Replaced')
            ->body("Replaced '{$fromItem->name}' with '{$toItem->name}' in {$replacedRows} photo(s), skipped {$deletedRows} (already had '{$toItem->name}')")
            ->success()
            ->send();

        $this->form->fill();
    }
}
