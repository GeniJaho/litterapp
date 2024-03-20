<?php

namespace App\Http\Controllers;

use App\Http\Requests\TagShortcuts\StoreTagShortcutRequest;
use App\Models\TagShortcut;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class TagShortcutsController extends Controller
{
    public function index(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $tagShortcuts = $user->tagShortcuts()
            ->orderBy('shortcut')
            ->with(['tagShortcutItems' => [
                'item:id,name', 'tags:id,name',
            ]])
            ->get();

        return Inertia::render('TagShortcuts/Index', [
            'tagShortcuts' => $tagShortcuts,
        ]);
    }

    public function show(TagShortcut $tagShortcut): JsonResponse
    {
        $tagShortcut->load(['tagShortcutItems' => [
            'item:id,name', 'tags:id,name',
        ]]);

        return response()->json([
            'tagShortcut' => $tagShortcut,
        ]);
    }

    public function store(StoreTagShortcutRequest $request)
    {
        /** @var User $user */
        $user = auth()->user();

        $tagShortcut = $user->tagShortcuts()->create([
            'shortcut' => $request->shortcut,
        ]);

        return response()->json([
            'tagShortcut' => $tagShortcut,
        ]);
    }

//    public function update(Request $request, string $id)
//    {
//        //
//    }
//
//    public function destroy(string $id)
//    {
//        //
//    }
}
