<?php

namespace App\Http\Controllers\TagShortcuts;

use App\Http\Controllers\Controller;
use App\Http\Requests\TagShortcuts\StoreTagShortcutItemRequest;
use App\Http\Requests\TagShortcuts\UpdateTagShortcutItemRequest;
use App\Models\TagShortcut;
use App\Models\TagShortcutItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class TagShortcutItemsController extends Controller
{
    public function store(TagShortcut $tagShortcut, StoreTagShortcutItemRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->id !== (int) $tagShortcut->user_id) {
            abort(404);
        }

        $tagShortcut->items()->attach($request->item_id, [
            'picked_up' => $user->settings->picked_up_by_default,
            'recycled' => $user->settings->recycled_by_default,
            'deposit' => $user->settings->deposit_by_default,
        ]);

        return response()->json();
    }

    public function update(TagShortcutItem $tagShortcutItem, UpdateTagShortcutItemRequest $request): JsonResponse
    {
        if (auth()->id() !== (int) $tagShortcutItem->tagShortcut->user_id) {
            abort(404);
        }

        if ($request->filled('quantity')) {
            $tagShortcutItem->quantity = $request->quantity;
        }

        if ($request->filled('picked_up')) {
            $tagShortcutItem->picked_up = $request->picked_up;
        }

        if ($request->filled('recycled')) {
            $tagShortcutItem->recycled = $request->recycled;
        }

        if ($request->filled('deposit')) {
            $tagShortcutItem->deposit = $request->deposit;
        }

        $tagShortcutItem->save();

        return response()->json();
    }

    public function destroy(TagShortcutItem $tagShortcutItem): JsonResponse
    {
        if (auth()->id() !== (int) $tagShortcutItem->tagShortcut->user_id) {
            abort(404);
        }

        $tagShortcutItem->delete();

        return response()->json();
    }
}
