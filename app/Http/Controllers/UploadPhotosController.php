<?php

namespace App\Http\Controllers;

use App\Actions\Photos\ExtractsLocationFromPhoto;
use App\Http\Requests\StorePhotosRequest;
use App\Models\Photo;

class UploadPhotosController extends Controller
{
    public function store(
        StorePhotosRequest $request,
        ExtractsLocationFromPhoto $extractLocation,
    ): array {
        $user = auth()->user();

        $photo = $request->file('photo');

        $location = $extractLocation->run($photo);

        $originalFileName = $photo->getClientOriginalName();

        Photo::create([
            'user_id' => $user->id,
            'path' => $photo->storeAs('photos', $originalFileName, 'public'),
            'latitude' => $location['latitude'] ?? null,
            'longitude' => $location['longitude'] ?? null,
        ]);

        return [];
    }
}
