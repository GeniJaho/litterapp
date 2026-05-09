<?php

use App\Actions\Photos\SuggestPhotoTagsAction;
use App\DTO\PhotoSuggestionResult;
use App\Models\AppSetting;
use App\Models\Photo;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

it('gets photo suggestions successfully', function (): void {
    Http::fake([
        '*/predict' => Http::response([
            'items' => [['id' => 1, 'name' => 'Bottle', 'confidence' => 0.95, 'count' => 10]],
            'brands' => [['id' => 5, 'name' => 'Coca-Cola', 'confidence' => 0.80, 'count' => 7]],
            'content' => [['id' => 10, 'name' => 'Soft drink', 'confidence' => 0.70, 'count' => 5]],
        ]),
    ]);

    $photo = Photo::factory()->create();
    $logSpy = Log::spy();

    $result = app(SuggestPhotoTagsAction::class)->run($photo);

    Http::assertSent(fn (Request $request): bool => $request->url() === config('services.litterbot.url').'/predict' &&
        $request['photo_url'] === $photo->full_path);

    expect($result)->toBeInstanceOf(PhotoSuggestionResult::class)
        ->items->toHaveCount(1)
        ->items->sequence(fn ($item) => $item->toMatchArray(['id' => 1, 'name' => 'Bottle', 'confidence' => 0.95, 'count' => 10]))
        ->brands->toHaveCount(1)
        ->content->toHaveCount(1);

    $logSpy->shouldHaveReceived('info')
        ->with('Received photo suggestions from LitterBot', [
            'photo_id' => $photo->id,
            'response' => [
                'items' => [['id' => 1, 'name' => 'Bottle', 'confidence' => 0.95, 'count' => 10]],
                'brands' => [['id' => 5, 'name' => 'Coca-Cola', 'confidence' => 0.80, 'count' => 7]],
                'content' => [['id' => 10, 'name' => 'Soft drink', 'confidence' => 0.70, 'count' => 5]],
            ],
        ]);
});

it('gets the litterbot url from settings', function (): void {
    AppSetting::query()->create([
        'key' => 'litterbot_url',
        'value' => 'https://litterbot.test',
    ]);
    Http::fake([
        '*/predict' => Http::response([
            'items' => [['id' => 1, 'name' => 'Bottle', 'confidence' => 0.95, 'count' => 10]],
            'brands' => [],
            'content' => [],
        ]),
    ]);

    $photo = Photo::factory()->create();

    $result = app(SuggestPhotoTagsAction::class)->run($photo);

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://litterbot.test/predict' &&
        $request['photo_url'] === $photo->full_path);

    expect($result)->toBeInstanceOf(PhotoSuggestionResult::class);
});

it('returns null when the HTTP request fails', function (): void {
    Http::fake([
        '*/predict' => Http::response('Error processing image', 500),
    ]);

    $photo = Photo::factory()->create();
    $logSpy = Log::spy();

    $result = app(SuggestPhotoTagsAction::class)->run($photo);

    expect($result)->toBeNull();

    $logSpy->shouldHaveReceived('error')
        ->with('Failed to get photo suggestions', [
            'photo_id' => $photo->id,
            'response' => 'Error processing image',
        ]);
});
