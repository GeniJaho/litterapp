<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\TagType;
use App\Models\User;
use Inertia\Inertia;

class PhotosController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = auth()->user();

        $photos = $user->photos()->latest()->paginate();

        $photos->getCollection()->transform(function (Photo $photo) {
            $photo->append('full_path');

            return $photo;
        });

        return Inertia::render('Photos', [
            'photos' => $photos,
        ]);
    }

    public function show(Photo $photo)
    {
        $tagTypes = TagType::query()->get();
        $tags = Tag::query()->get()
            ->groupBy('tag_type_id')
            ->mapWithKeys(function ($values, $key) use ($tagTypes) {
                return [$tagTypes->find($key)->name => $values];
            });

        if (! request()->wantsJson()) {
            return Inertia::render('ShowPhoto', [
                'photoId' => $photo->id,
                'items' => Item::all(),
                'tags' => $tags,
            ]);
        }

        $photo->load('items');
        $photo->items->each(fn (Item $item) => $item->pivot->load('tags'));
        $photo->append('full_path');

        return $photo;
    }
}
