<?php

namespace App\Http\Controllers;

use App\Actions\Photos\GetTagsAndItemsAction;
use App\Http\Requests\TagShortcuts\StoreTagShortcutRequest;
use App\Http\Requests\TagShortcuts\UpdateTagShortcutRequest;
use App\Models\TagShortcut;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class TagShortcutsController extends Controller
{
    public function index(GetTagsAndItemsAction $getTagsAndItemsAction): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $tagShortcuts = $user->tagShortcuts()
            ->orderBy('shortcut')
            ->with(['tagShortcutItems' => [
                'item:id,name', 'tags:id,name',
            ]])
            ->get();

        $tagsAndItems = $getTagsAndItemsAction->run();

        return Inertia::render('TagShortcuts/Index', [
            'tagShortcuts' => $tagShortcuts,
            'items' => $tagsAndItems['items'],
            'tags' => $tagsAndItems['tags'],
        ]);
    }

    public function store(StoreTagShortcutRequest $request): \Illuminate\Http\JsonResponse
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

    public function update(TagShortcut $tagShortcut, UpdateTagShortcutRequest $request): \Illuminate\Http\JsonResponse
    {
        $tagShortcut->update([
            'shortcut' => $request->shortcut,
        ]);

        $tagShortcut->load(['tagShortcutItems' => [
            'item:id,name', 'tags:id,name',
        ]]);

        return response()->json([
            'tagShortcut' => $tagShortcut,
        ]);
    }

    public function destroy(TagShortcut $tagShortcut): void
    {
        if (auth()->id() !== $tagShortcut->user_id) {
            abort(403);
        }

        $tagShortcut->delete();
    }
}
