<?php

namespace App\Http\Controllers;

use App\Actions\Photos\GetTagsAndItemsAction;
use App\Http\Requests\TagShortcuts\StoreTagShortcutRequest;
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

//    public function show(TagShortcut $tagShortcut, GetTagsAndItemsAction $getTagsAndItemsAction): JsonResponse
//    {
//        $tagShortcut->load(['tagShortcutItems' => [
//            'item:id,name', 'tags:id,name',
//        ]]);
//
//        $tagsAndItems = $getTagsAndItemsAction->run();
//
//        return response()->json([
//            'tagShortcut' => $tagShortcut,
//            'items' => $tagsAndItems['items'],
//            'tags' => $tagsAndItems['tags'],
//        ]);
//    }

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
