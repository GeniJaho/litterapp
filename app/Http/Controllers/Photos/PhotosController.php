<?php

namespace App\Http\Controllers\Photos;

use App\Actions\Photos\FilterPhotosAction;
use App\Actions\Photos\GetNextPhotoAction;
use App\Actions\Photos\GetPreviousPhotoAction;
use App\Actions\Photos\GetTagsAndItemsAction;
use App\DTO\PhotoFilters;
use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Models\TagShortcut;
use App\Models\User;
use Illuminate\Container\Attributes\Config;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
        } elseif ($request->boolean('set_per_page')) {
            $perPage = in_array($request->integer('per_page'), [25, 50, 100, 200])
                ? $request->integer('per_page')
                : 25;
            $user->settings->per_page = $perPage;
            $user->save();
        } elseif ($request->boolean('set_sort')) {
            $sortColumn = in_array($request->string('sort_column'), ['id', 'taken_at_local', 'original_file_name'])
                ? $request->string('sort_column')
                : 'id';
            $sortDirection = in_array($request->string('sort_direction'), ['asc', 'desc'])
                ? $request->string('sort_direction')
                : 'desc';
            $user->settings->sort_column = $sortColumn;
            $user->settings->sort_direction = $sortDirection;
            $user->save();
        }

        $photos = $filterPhotosAction->run($user);

        $tagsAndItems = $getTagsAndItemsAction->run();

        $users = $user->is_admin
            ? User::query()->whereHas('photos')->select(['id', 'name', 'email'])->orderBy('name')->get()
            : [];

        return Inertia::render('Photos/Index', [
            'photos' => $photos,
            'filters' => $user->settings->photo_filters,
            'items' => $tagsAndItems['items'],
            'tags' => $tagsAndItems['tags'],
            'tagShortcuts' => $this->getTagShortcuts($user),
            'users' => $users,
            'isAdmin' => $user->is_admin,
        ]);
    }

    public function show(
        Photo $photo,
        GetTagsAndItemsAction $getTagsAndItemsAction,
        GetNextPhotoAction $getNextPhotoAction,
        GetPreviousPhotoAction $getPreviousPhotoAction,
        #[Config('services.litterbot.enabled')] bool $litterBotEnabled,
    ): Response|JsonResponse {
        /** @var User $user */
        $user = auth()->user();

        if ($user->id !== $photo->user_id && ! $user->is_admin) {
            abort(404);
        }

        if (! request()->wantsJson()) {
            $tagsAndItems = $getTagsAndItemsAction->run();

            return Inertia::render('Photos/Show', [
                'photoId' => $photo->id,
                'items' => $tagsAndItems['items'],
                'tags' => $tagsAndItems['tags'],
                'nextPhotoUrl' => $getNextPhotoAction->run($user, $photo),
                'previousPhotoUrl' => $getPreviousPhotoAction->run($user, $photo),
                'tagShortcuts' => $this->getTagShortcuts($user),
                'suggestionsEnabled' => $litterBotEnabled && $user->is_admin,
            ]);
        }

        $photo
            ->append('full_path')
            ->load([
                'photoItems' => fn (Builder $q) => $q
                    ->with('item:id,name')
                    ->with('tags:id,name')
                    ->orderByDesc('id'),
                'photoItemSuggestions.item:id,name',
            ]);

        return response()->json([
            'photo' => $photo,
        ]);
    }

    public function destroy(Photo $photo): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->id !== $photo->user_id && ! $user->is_admin) {
            abort(404);
        }

        $photo->delete();

        Storage::delete($photo->path);

        return redirect()->route('my-photos');
    }

    /**
     * @return Collection<int, TagShortcut>
     */
    public function getTagShortcuts(User $user): Collection
    {
        return $user
            ->tagShortcuts()
            ->whereHas('tagShortcutItems')
            ->with(TagShortcut::commonEagerLoads())
            ->orderBy('shortcut')
            ->get();
    }
}
