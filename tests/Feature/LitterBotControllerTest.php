<?php

use App\Actions\Photos\ClassifiesPhoto;
use App\DTO\PhotoItemPrediction;
use App\Models\Item;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Doubles\FakeClassifyPhotoAction;

test('it suggests an item for a photo', function (): void {
    $this->actingAs($user = admin());
    $photo = Photo::factory()->for($user)->create();
    $item = Item::factory()->create(['name' => 'Bottle']);

    $this->swap(ClassifiesPhoto::class, (new FakeClassifyPhotoAction)->shouldReturnPrediction(
        new PhotoItemPrediction('bottle', 0.95)
    ));

    $response = $this->getJson(route('litterbot.suggest', $photo));

    $response->assertOk();
    $response->assertJson(fn (AssertableJson $json): AssertableJson => $json
        ->has('id')
        ->where('item.id', $item->id)
        ->where('item.name', $item->name)
        ->where('score', 0.95)
        ->etc()
    );
});

test('it returns error when classification fails', function (): void {
    $this->actingAs($user = admin());
    $photo = Photo::factory()->for($user)->create();

    $this->swap(ClassifiesPhoto::class, (new FakeClassifyPhotoAction)->shouldFail());

    $response = $this->getJson(route('litterbot.suggest', $photo));

    $response->assertStatus(422);
    $response->assertJson(['error' => 'Failed to connect to LitterBot service']);
});

test('it returns empty response when item not found', function (): void {
    $this->actingAs($user = User::factory()->create());
    $photo = Photo::factory()->for($user)->create();

    $this->swap(ClassifiesPhoto::class, (new FakeClassifyPhotoAction)->shouldReturnPrediction(
        new PhotoItemPrediction('unknown-item', 0.95)
    ));

    $response = $this->getJson(route('litterbot.suggest', $photo));

    $response->assertOk();
    $response->assertJson([]);
});

test('it returns empty response when item already exists in photo', function (): void {
    $this->actingAs($user = admin());
    $photo = Photo::factory()->for($user)->create();
    $item = Item::factory()->create(['name' => 'Bottle']);
    $photo->items()->attach($item);

    $this->swap(ClassifiesPhoto::class, (new FakeClassifyPhotoAction)->shouldReturnPrediction(
        new PhotoItemPrediction('bottle', 0.95)
    ));

    $response = $this->getJson(route('litterbot.suggest', $photo));

    $response->assertOk();
    $response->assertJson([]);
});

test('it returns 404 when photo does not belong to user', function (): void {
    $this->actingAs($user = admin());
    $otherUser = User::factory()->create();
    $photo = Photo::factory()->for($otherUser)->create();

    $this->swap(ClassifiesPhoto::class, new FakeClassifyPhotoAction);

    $response = $this->getJson(route('litterbot.suggest', $photo));

    $response->assertNotFound();
});
