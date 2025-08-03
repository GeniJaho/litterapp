<?php

namespace App\Http\Controllers\Photos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Photos\StorePhotosRequest;
use App\Jobs\SuggestPhotoItem;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;

class UploadPhotosController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Upload');
    }

    public function store(
        StorePhotosRequest $request,
        #[Config('services.litterbot.enabled')] bool $litterBotEnabled
    ): JsonResponse {
        /** @var User $user */
        $user = auth()->user();

        /** @var UploadedFile $photo */
        $photo = $request->file('photo');

        $exif = $request->getExifData();

        $path = $photo->store('photos');

        $photo = Photo::create([
            'user_id' => $user->id,
            'path' => $path,
            'original_file_name' => $photo->getClientOriginalName(),
            'latitude' => $exif['latitude'] ?? null,
            'longitude' => $exif['longitude'] ?? null,
            'taken_at_local' => $exif['taken_at_local'] ?? null,
        ]);

        if ($litterBotEnabled && $user->is_admin && $photo->photoItemSuggestions()->doesntExist()) {
            SuggestPhotoItem::dispatch($photo);
        }

        return response()->json();
    }
}
