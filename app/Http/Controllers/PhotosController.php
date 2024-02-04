<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\TagType;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PhotosController extends Controller
{
    public function index(): Response
    {

        /** @var User $user */
        $user = auth()->user();
        $itemFilter = request()->input('item');



        $photos = $user->photos()
            ->withExists('items')
            ->latest('id')
            ->paginate(12);

        $photos->getCollection()->transform(function (Photo $photo) {
            $photo->append('full_path');

            return $photo;
        });

        $tagTypes = TagType::query()->get();
        $tags = Tag::query()
            ->orderBy('name')
            ->get()
            ->groupBy('tag_type_id')
            ->mapWithKeys(function ($values, $key) use ($tagTypes) {
                /** @var TagType $tagType */
                $tagType = $tagTypes->find($key);

                return [$tagType->slug => $values];
            });

        return Inertia::render('Photos', [
            'photos' => $photos,
            'items' => Item::query()->orderBy('name')->get(),
            'tags' => $tags,
        ]);
    }

    public function show(Photo $photo): Response|JsonResponse
    {
        if (auth()->id() !== $photo->user_id) {
            abort(404);
        }

        $tagTypes = TagType::query()->get();
        $tags = Tag::query()
            ->orderBy('name')
            ->get()
            ->groupBy('tag_type_id')
            ->mapWithKeys(function ($values, $key) use ($tagTypes) {
                /** @var TagType $tagType */
                $tagType = $tagTypes->find($key);

                return [$tagType->slug => $values];
            });

        if (! request()->wantsJson()) {
            return Inertia::render('Photo/Show', [
                'photoId' => $photo->id,
                'items' => Item::query()->orderBy('name')->get(),
                'tags' => $tags,
                'nextPhotoUrl' => $this->getNextPhotoUrl($photo),
                'previousPhotoUrl' => $this->getPreviousPhotoUrl($photo),
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

        Storage::disk('public')->delete($photo->path);

        return redirect()->route('my-photos');
    }

    private function getNextPhotoUrl(Photo $photo): ?string
    {
        $nextPhoto = Photo::query()
            ->where('user_id', $photo->user_id)
            ->whereDoesntHave('items')
            ->where('id', '<', $photo->id)
            ->orderByDesc('id')
            ->first();

        if (! $nextPhoto) {
            return null;
        }

        return route('photos.show', $nextPhoto);
    }

    private function getPreviousPhotoUrl(Photo $photo): ?string
    {
        $previousPhoto = Photo::query()
            ->where('user_id', $photo->user_id)
            ->whereDoesntHave('items')
            ->where('id', '>', $photo->id)
            ->orderBy('id')
            ->first();

        if (! $previousPhoto) {
            return null;
        }

        return route('photos.show', $previousPhoto);
    }
}
