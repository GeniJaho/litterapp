<?php

namespace App\Http\Controllers\Photos;

use App\Actions\Photos\StorePhotoAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Photos\StorePhotosRequest;
use App\Jobs\SuggestPhotoItem;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
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
        #[CurrentUser] User $user,
        StorePhotosRequest $request,
        StorePhotoAction $storePhoto,
    ): JsonResponse {
        /** @var UploadedFile $photo */
        $photo = $request->file('photo');

        $exif = $request->getExifData();

        $photo = $storePhoto->run($photo, $user, $exif);

        if ($user->settings->litterbot_enabled) {
            SuggestPhotoItem::dispatch($photo);
        }

        return response()->json();
    }
}
