<?php

namespace App\Http\Controllers;

use App\Models\User;
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

//    public function store(Request $request)
//    {
//        //
//    }
//
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
