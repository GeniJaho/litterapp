<?php

namespace App\Http\Controllers\Photos;

use App\Actions\Photos\FilterPhotosAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhotoIdsController extends Controller
{
    public function __invoke(Request $request, FilterPhotosAction $filterPhotosAction): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $limit = (int) $request->query('limit', $user->settings->getValidPerPage());

        return response()->json($filterPhotosAction->idsUpToPage($user, $limit));
    }
}
