<?php

namespace App\Http\Controllers\Groups;

use App\Http\Controllers\Controller;
use App\Http\Requests\Groups\StoreGroupPhotosRequest;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupPhotosController extends Controller
{
    public function store(Group $group, StoreGroupPhotosRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->id !== $group->user_id) {
            abort(404);
        }

        $group->photos()->syncWithoutDetaching($request->photo_ids);

        return response()->json();
    }
}
