<?php

use App\Filament\Resources\TagResource\Pages\ListTags;
use App\Models\TagType;
use Filament\Facades\Filament;
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
        ->assertSeeHtml('x-on:keydown.enter.prevent')
        ->assertSeeHtml('autofocus');
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
});
