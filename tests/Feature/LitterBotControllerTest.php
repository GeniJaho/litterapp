<?php

use App\Actions\Photos\SuggestsPhotoTags;
use App\DTO\PhotoSuggestionResult;
use App\DTO\UserSettings;
use App\Models\Item;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Doubles\FakeSuggestPhotoTagsAction;

test('it suggests an item for a photo', function (): void {
    $user = User::factory()->create(['settings' => new UserSettings(litterbot_enabled: true)]);
    $this->actingAs($user);
    $photo = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();

    $this->swap(SuggestsPhotoTags::class, (new FakeSuggestPhotoTagsAction)->shouldReturnResult(
        new PhotoSuggestionResult(
            items: [['id' => $item->id, 'name' => $item->name, 'confidence' => 0.95, 'count' => 10]],
            brands: [],
            content: [],
        )
    ));

    $response = $this->getJson(route('litterbot.suggest', $photo));

    $response->assertOk();
    $response->assertJson(fn (AssertableJson $json): AssertableJson => $json
        ->has('suggestion.id')
        ->where('suggestion.item.id', $item->id)
        ->where('suggestion.item.name', $item->name)
        ->where('suggestion.item_score', 95)
        ->where('suggestion.item_count', 10)
        ->etc()
    );
});

test('it returns existing suggestion if available', function (): void {
    $user = User::factory()->create(['settings' => new UserSettings(litterbot_enabled: true)]);
    $this->actingAs($user);
    $photo = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $photo->photoSuggestions()->create([
        'item_id' => $item->id,
        'item_score' => 95,
        'item_count' => 10,
    ]);

    $response = $this->getJson(route('litterbot.suggest', $photo));

    $response->assertOk();
    $response->assertJson(fn (AssertableJson $json): AssertableJson => $json
        ->has('suggestion.id')
        ->where('suggestion.item.id', $item->id)
        ->where('suggestion.item.name', $item->name)
        ->where('suggestion.item_score', 95)
        ->etc()
    );
});

test('it returns error when action fails', function (): void {
    $user = User::factory()->create(['settings' => new UserSettings(litterbot_enabled: true)]);
    $this->actingAs($user);
    $photo = Photo::factory()->for($user)->create();

    $this->swap(SuggestsPhotoTags::class, (new FakeSuggestPhotoTagsAction)->shouldFail());

    $response = $this->getJson(route('litterbot.suggest', $photo));

    $response->assertStatus(422);
    $response->assertJson(['error' => 'Failed to connect to LitterBot service']);
});

test('it returns empty response when item already exists in photo', function (): void {
    $user = User::factory()->create(['settings' => new UserSettings(litterbot_enabled: true)]);
    $this->actingAs($user);
    $photo = Photo::factory()->for($user)->create();
    $item = Item::factory()->create();
    $photo->items()->attach($item);

    $this->swap(SuggestsPhotoTags::class, (new FakeSuggestPhotoTagsAction)->shouldReturnResult(
        new PhotoSuggestionResult(
            items: [['id' => $item->id, 'name' => $item->name, 'confidence' => 0.95, 'count' => 10]],
            brands: [],
            content: [],
        )
    ));

    $response = $this->getJson(route('litterbot.suggest', $photo));

    $response->assertOk();
    $response->assertJson([]);
});

test('it returns 404 when photo does not belong to user', function (): void {
    $this->actingAs(User::factory()->create(['settings' => new UserSettings(litterbot_enabled: true)]));
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->for($otherUser)->create();

    $response = $this->getJson(route('litterbot.suggest', $photo));

    $response->assertNotFound();
});

test('it returns empty response when LitterBot is disabled', function (): void {
    $user = User::factory()->create(['settings' => new UserSettings(litterbot_enabled: false)]);
    $this->actingAs($user);
    $photo = Photo::factory()->for($user)->create();

    $response = $this->getJson(route('litterbot.suggest', $photo));

    $response->assertOk();
    $response->assertJson([]);
});
