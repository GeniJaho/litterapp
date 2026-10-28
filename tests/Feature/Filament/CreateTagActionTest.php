<?php

use App\Filament\Resources\TagResource\Pages\ListTags;
use App\Models\Tag;
use App\Models\TagType;
use Filament\Facades\Filament;
use Filament\Tables\Actions\EditAction;
use Livewire\Livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(admin());
});

it('focuses the name field when creating another tag', function (): void {
    $tagType = TagType::factory()->create();

    Livewire::test(ListTags::class)
        ->callAction('create', data: [
            'tag_type_id' => $tagType->id,
            'name' => 'Brand One',
        ], arguments: ['another' => true])
        ->assertHasNoActionErrors()
        ->assertDispatched('focus-tag-name')
        ->assertSeeHtml('x-on:focus-tag-name.window')
        ->assertSeeHtml('x-on:keydown.enter.prevent="$wire.callMountedAction({ another: true })"')
        ->assertSeeHtml('autofocus');

    $this->assertDatabaseHas('tags', [
        'tag_type_id' => $tagType->id,
        'name' => 'Brand One',
    ]);
});

it('does not refocus the name field after a normal tag creation', function (): void {
    $tagType = TagType::factory()->create();

    Livewire::test(ListTags::class)
        ->callAction('create', data: [
            'tag_type_id' => $tagType->id,
            'name' => 'Brand One',
        ])
        ->assertHasNoActionErrors()
        ->assertNotDispatched('focus-tag-name');

    $this->assertDatabaseHas('tags', [
        'tag_type_id' => $tagType->id,
        'name' => 'Brand One',
    ]);
});

it('keeps the default enter behaviour in the edit modal', function (): void {
    $tag = Tag::factory()->create();

    Livewire::test(ListTags::class)
        ->mountTableAction(EditAction::class, $tag)
        ->assertSeeHtml('wire:model="mountedTableActionsData.0.name"')
        ->assertDontSeeHtml('callMountedAction({ another: true })')
        ->assertDontSeeHtml('x-on:focus-tag-name.window');
});
