<?php

namespace App\Filament\Resources\TagResource\Pages;

use App\Actions\Photos\GetTagsAndItemsAction;
use App\Filament\Resources\TagResource;
use App\Models\Tag;
use App\Models\TagType;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class ListTags extends ListRecords
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalSubmitAction(fn (Action $action): Action => $action->color('gray'))
                ->extraModalFooterActions(fn (CreateAction $action): array => [
                    $action->makeModalSubmitAction('createAnother', arguments: ['another' => true])
                        ->label(__('filament-actions::create.single.modal.actions.create_another.label'))
                        ->color('primary'),
                ])
                ->after(function (array $arguments, ListTags $livewire): void {
                    Cache::forget(GetTagsAndItemsAction::CACHE_KEY);

                    if ($arguments['another'] ?? false) {
                        $livewire->dispatch('focus-tag-name');
                    }
                }),
        ];
    }

    public function getTabs(): array
    {
        /** @var Tab[] $tagTypes */
        $tagTypes = TagType::query()->get()
            ->mapWithKeys(fn (TagType $tagType): array => [
                $tagType->slug => Tab::make($tagType->name)
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('tag_type_id', $tagType->id))
                    ->badge(Tag::query()->where('tag_type_id', $tagType->id)->count()),
            ])
            ->toArray();

        return [
            'all' => Tab::make()->badge(Tag::query()->count()),
            ...$tagTypes,
        ];
    }
}
