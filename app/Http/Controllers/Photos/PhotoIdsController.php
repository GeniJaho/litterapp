<?php

namespace App\Http\Controllers\Photos;

use App\Actions\Photos\FilterPhotosAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PhotoIdsController extends Controller
{
    public function __invoke(FilterPhotosAction $filterPhotosAction): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        return response()->json($filterPhotosAction->allIds($user));
    }
}
