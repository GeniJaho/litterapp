<?php

namespace App\Http\Controllers;

use App\Actions\Photos\GetTagsAndItemsAction;
use App\Models\Item;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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
        GetTagsAndItemsAction $getTagsAndItemsAction,
    ): Response {
        $filters = $request->all();
        $filterItemIds = $filters['item_ids'] ?? [];
        $filterTagIds = array_map(fn ($id) => (int) $id, $filters['tag_ids'] ?? []);
        $uploadedFrom = $filters['uploaded_from'] ?? null;
        $uploadedUntil = $filters['uploaded_until'] ?? null;
        $takenFromLocal = $filters['taken_from_local'] ?? null;
        $takenUntilLocal = $filters['taken_until_local'] ?? null;
        $allFilters = [
            'item_ids' => $filterItemIds,
            'tag_ids' => $filterTagIds,
            'uploaded_from' => $uploadedFrom,
            'uploaded_until' => $uploadedUntil,
            'taken_from_local' => $takenFromLocal,
            'taken_until_local' => $takenUntilLocal,
        ];

        /** @var User $user */
        $user = auth()->user();

        $photos = $user
            ->photos()
            ->withExists('items')
            ->when($filterItemIds !== [], fn (Builder $query) => $query
                ->whereHas('items', fn (Builder $query) => $query
                    ->whereIn('item_id', $filterItemIds)
                )
            )
            ->when($filterTagIds !== [], fn (Builder $query) => $query
                ->whereHas('items', fn (Builder $query) => $query
                    ->join('photo_item_tag', 'photo_items.id', '=', 'photo_item_tag.photo_item_id')
                    ->whereIn('photo_item_tag.tag_id', $filterTagIds)
                )
            )
            ->when($uploadedFrom, fn ($query) => $query->where('created_at', '>=', $uploadedFrom))
            ->when($uploadedUntil, fn ($query) => $query->where('created_at', '<=', $uploadedUntil))
            ->when($takenFromLocal, fn ($query) => $query->whereDate('taken_at_local', '>=', $takenFromLocal))
            ->when($takenUntilLocal, fn ($query) => $query->whereDate('taken_at_local', '<=', $takenUntilLocal))
            ->latest('id')
            ->paginate(12);

        $photos->getCollection()->transform(function (Photo $photo) {
            $photo->append('full_path');

            return $photo;
        });

        $tagsAndItems = $getTagsAndItemsAction->run();

        return Inertia::render('Photos', [
            'photos' => $photos,
            'items' => $tagsAndItems['items'],
            'tags' => $tagsAndItems['tags'],
            'filters' => $allFilters,
        ]);
    }

    public function show(
        Photo $photo,
        GetTagsAndItemsAction $getTagsAndItemsAction,
    ): Response|JsonResponse {
        if (auth()->id() !== $photo->user_id) {
            abort(404);
        }

        if (! request()->wantsJson()) {
            $tagsAndItems = $getTagsAndItemsAction->run();

            return Inertia::render('Photo/Show', [
                'photoId' => $photo->id,
                'items' => $tagsAndItems['items'],
                'tags' => $tagsAndItems['tags'],
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

        Storage::delete($photo->path);

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
