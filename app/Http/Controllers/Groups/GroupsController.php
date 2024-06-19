<?php

namespace App\Http\Controllers\Groups;

use App\Http\Controllers\Controller;
use App\Http\Requests\Groups\StoreGroupRequest;
use App\Http\Requests\Groups\UpdateGroupRequest;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class GroupsController extends Controller
{
    public function store(StoreGroupRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $group = $user->groups()->create([
            'name' => $request->name,
        ]);

        return response()->json([
            'group' => $group,
        ]);
    }

    public function update(Group $group, UpdateGroupRequest $request): JsonResponse
    {
        $group->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'group' => $group,
        ]);
    }

    public function destroy(Group $group): void
    {
        if (auth()->id() !== (int) $group->user_id) {
            abort(404);
        }

        $group->delete();
    }
}
