<?php

namespace App\Http\Controllers;

use App\Actions\Photos\FilterPhotosAction;
use App\Actions\Photos\GetTagsAndItemsAction;
use App\DTO\PhotoFilters;
use App\Models\Item;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PhotosController extends Controller
{
    public function index(
        Request $request,
        PhotoFilters $photoFilters,
        GetTagsAndItemsAction $getTagsAndItemsAction,
        FilterPhotosAction $filterPhotosAction,
    ): Response {
        /** @var User $user */
        $user = auth()->user();

        if ($request->boolean('store_filters')) {
            $user->settings->photo_filters = $photoFilters;
            $user->save();
        } elseif ($request->boolean('clear_filters')) {
            $user->settings->photo_filters = null;
            $user->save();
        }

        $photos = $filterPhotosAction->run($user);

        $tagsAndItems = $getTagsAndItemsAction->run();

        return Inertia::render('Photos', [
            'photos' => $photos,
            'filters' => $user->settings->photo_filters,
            'items' => $tagsAndItems['items'],
            'tags' => $tagsAndItems['tags'],
        ]);
    }

    public function show(
        Photo $photo,
        GetTagsAndItemsAction $getTagsAndItemsAction,
    ): Response|JsonResponse {
        /** @var User $user */
        $user = auth()->user();

        if ($user->id !== $photo->user_id) {
            abort(404);
        }

        if (! request()->wantsJson()) {
            $tagsAndItems = $getTagsAndItemsAction->run();

            return Inertia::render('Photo/Show', [
                'photoId' => $photo->id,
                'items' => $tagsAndItems['items'],
                'tags' => $tagsAndItems['tags'],
                'nextPhotoUrl' => $this->getNextPhotoUrl($user, $photo),
                'previousPhotoUrl' => $this->getPreviousPhotoUrl($user, $photo),
            ]);
        }

        $items = $photo
            ->items()
            ->orderByDesc('photo_items.id')
            ->get()
            ->each(fn (Item $item) => $item->pivot?->load('tags'));

        $photo->append('full_path');

        return response()->json([
            'photo' => $photo,
            'items' => $items,
        ]);
    }

    public function destroy(Photo $photo): RedirectResponse
    {
        if (auth()->id() !== $photo->user_id) {
            abort(404);
        }

        $photo->delete();

        Storage::delete($photo->path);

        return redirect()->route('my-photos');
    }

    private function getNextPhotoUrl(User $user, Photo $photo): ?string
    {
        $nextPhoto = $user
            ->photos()
            ->filter($user->settings->photo_filters)
            ->where('id', '<', $photo->id)
            ->orderByDesc('id')
            ->first();

        if (! $nextPhoto) {
            return null;
        }

        return route('photos.show', $nextPhoto);
    }

    private function getPreviousPhotoUrl(User $user, Photo $photo): ?string
    {
        $previousPhoto = $user
            ->photos()
            ->filter($user->settings->photo_filters)
            ->where('id', '>', $photo->id)
            ->orderBy('id')
            ->first();

        if (! $previousPhoto) {
            return null;
        }

        return route('photos.show', $previousPhoto);
    }
}
