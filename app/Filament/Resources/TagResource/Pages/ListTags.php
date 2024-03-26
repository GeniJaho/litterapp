<?php

namespace App\Filament\Resources\TagResource\Pages;

use App\Filament\Resources\TagResource;
use App\Models\Tag;
use App\Models\TagType;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTags extends ListRecords
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
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
