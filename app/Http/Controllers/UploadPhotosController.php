<?php

namespace App\Http\Controllers;

use App\Actions\Photos\ExtractsExifFromPhoto;
use App\Http\Requests\StorePhotosRequest;
use App\Models\Photo;
use App\Models\User;
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
        ExtractsExifFromPhoto $extractExif,
    ): JsonResponse {
        /** @var User $user */
        $user = auth()->user();

        /** @var UploadedFile $photo */
        $photo = $request->file('photo');

        $exif = $extractExif->run($photo);

        $path = $photo->store('photos');

        Photo::create([
            'user_id' => $user->id,
            'path' => $path,
            'original_file_name' => $photo->getClientOriginalName(),
            'latitude' => $exif['latitude'] ?? null,
            'longitude' => $exif['longitude'] ?? null,
            'taken_at_local' => $exif['taken_at_local'] ?? null,
        ]);

        return response()->json();
    }
}
