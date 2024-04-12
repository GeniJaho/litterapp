<?php

namespace App\Http\Controllers\TagShortcuts;

use App\Actions\Photos\GetTagsAndItemsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\TagShortcuts\StoreTagShortcutRequest;
use App\Http\Requests\TagShortcuts\UpdateTagShortcutRequest;
use App\Models\TagShortcut;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class TagShortcutsController extends Controller
{
    public function index(GetTagsAndItemsAction $getTagsAndItemsAction): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $tagShortcuts = $user
            ->tagShortcuts()
            ->with(TagShortcut::commonEagerLoads())
            ->orderBy('shortcut')
            ->get();

        $tagsAndItems = $getTagsAndItemsAction->run(withTrashed: false);

        return Inertia::render('TagShortcuts/Index', [
            'tagShortcuts' => $tagShortcuts,
            'items' => $tagsAndItems['items'],
            'tags' => $tagsAndItems['tags'],
        ]);
    }

    public function show(TagShortcut $tagShortcut): JsonResponse
    {
        if (auth()->id() !== (int) $tagShortcut->user_id) {
            abort(404);
        }

        $tagShortcut->load(TagShortcut::commonEagerLoads());

        return response()->json([
            'tagShortcut' => $tagShortcut,
        ]);
    }

    public function store(StoreTagShortcutRequest $request): JsonResponse
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

    public function update(TagShortcut $tagShortcut, UpdateTagShortcutRequest $request): JsonResponse
    {
        $tagShortcut->update([
            'shortcut' => $request->shortcut,
        ]);

        $tagShortcut->load(TagShortcut::commonEagerLoads());

        return response()->json([
            'tagShortcut' => $tagShortcut,
        ]);
    }

    public function destroy(TagShortcut $tagShortcut): void
    {
        if (auth()->id() !== (int) $tagShortcut->user_id) {
            abort(404);
        }

        $tagShortcut->delete();
    }
}
