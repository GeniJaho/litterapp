<?php

namespace App\Http\Controllers;

use App\Actions\Photos\ExtractsLocationFromPhoto;
use App\Http\Requests\StorePhotosRequest;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;

class UploadPhotosController extends Controller
{
    public function store(
        StorePhotosRequest $request,
        ExtractsLocationFromPhoto $extractLocation,
    ): JsonResponse {
        /** @var User $user */
        $user = auth()->user();

        /** @var UploadedFile $photo */
        $photo = $request->file('photo');

        $location = $extractLocation->run($photo);

        $path = $photo->store('photos', 's3');

        Photo::create([
            'user_id' => $user->id,
            'path' => $path,
            'original_file_name' => $photo->getClientOriginalName(),
            'latitude' => $location['latitude'] ?? null,
            'longitude' => $location['longitude'] ?? null,
        ]);

        return response()->json();
    }
}
