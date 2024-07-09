<?php

use App\Actions\Photos\ExportPhotosAction;
use App\DTO\PhotoExport;
use App\DTO\PhotoFilters;
use App\DTO\UserSettings;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Collection;

use function Pest\Laravel\freezeTime;

it('exports photos with items and tags', function (): void {
    freezeTime();
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id]);
    $photoItem = PhotoItem::factory()->create([
        'photo_id' => $photo->id,
        'picked_up' => true,
        'recycled' => false,
        'deposit' => true,
        'quantity' => 2,
    ]);
    $tag = Tag::factory()->create();
    $photoItem->tags()->attach($tag->id);

    $exportPhotosAction = new ExportPhotosAction();
    $result = $exportPhotosAction->run($user);

    expect($result)->toBeInstanceOf(Generator::class);

    $result = iterator_to_array($result);

    expect($result)->toHaveCount(1)
        ->and($result[0])->toBeInstanceOf(PhotoExport::class)
        ->id->toBe($photo->id)
        ->original_file_name->toBe($photo->original_file_name)
        ->latitude->toBe($photo->latitude)
        ->longitude->toBe($photo->longitude)
        ->taken_at_local->toBe($photo->taken_at_local)
        ->created_at->toEqual($photo->created_at->toIso8601String())
        ->items->toBeInstanceOf(Collection::class)
        ->items->toHaveCount(1)
        ->items->first()->toBe([
            'name' => $photoItem->item->name,
            'picked_up' => $photoItem->picked_up,
            'recycled' => $photoItem->recycled,
            'deposit' => $photoItem->deposit,
            'quantity' => $photoItem->quantity,
            'tags' => [[
                'type' => $tag->type->name,
                'name' => $tag->name,
            ]],
        ]);
});

it('follows user defined filters when returning a response', function (): void {
    freezeTime();
    $user = User::factory()->create([
        'settings' => new UserSettings(photo_filters: new PhotoFilters(picked_up: true)),
    ]);

    $pickedUpPhoto = Photo::factory()->create(['user_id' => $user->id]);
    $pickedUpPhotoItem = PhotoItem::factory()->create(['photo_id' => $pickedUpPhoto->id, 'picked_up' => true]);

    $notPickedUpPhoto = Photo::factory()->create(['user_id' => $user->id]);
    $notPickedUpPhotoItem = PhotoItem::factory()->create(['photo_id' => $notPickedUpPhoto->id, 'picked_up' => false]);

    $exportPhotosAction = new ExportPhotosAction();
    $result = $exportPhotosAction->run($user);

    expect($result)->toBeInstanceOf(Generator::class);

    $result = iterator_to_array($result);

    expect($result)->toHaveCount(1)
        ->and($result[0]->id)->toBe($pickedUpPhoto->id);
});
