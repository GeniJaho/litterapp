<?php

use App\Actions\Photos\ClassifyPhotoAction;
use App\DTO\PhotoItemPrediction;
use App\Models\Photo;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

it('classifies a photo successfully', function (): void {
    Http::fake([
        '*/predict' => Http::response(['class_name' => 'bottle', 'score' => 0.95]),
    ]);

    $photo = Photo::factory()->create();

    $result = app(ClassifyPhotoAction::class)->run($photo);

    Http::assertSent(function (Request $request) use ($photo) {
        return $request->url() === config('services.litterbot.url').'/predict' &&
            $request['image_path'] === $photo->full_path;
    });

    expect($result)->toBeInstanceOf(PhotoItemPrediction::class)
        ->class_name->toBe('bottle')
        ->score->toBe(0.95);
});

it('returns null when the HTTP request fails', function (): void {
    Http::fake([
        '*/predict' => Http::response('Error processing image', 500),
    ]);
    $logSpy = Log::spy();

    $photo = Photo::factory()->create();

    $result = app(ClassifyPhotoAction::class)->run($photo);

    expect($result)->toBeNull();

    // Assert the error was logged
    $logSpy->shouldHaveReceived('error')
        ->with('Failed to get image prediction', [
            'photo_id' => $photo->id,
            'response' => 'Error processing image',
        ]);
});
