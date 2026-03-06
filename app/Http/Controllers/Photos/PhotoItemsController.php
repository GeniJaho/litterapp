<?php

namespace App\Http\Controllers\Photos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Photos\StorePhotoItemRequest;
use App\Http\Requests\Photos\UpdatePhotoItemRequest;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\PhotoSuggestion;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PhotoItemsController extends Controller
{
    public function store(Photo $photo, StorePhotoItemRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->id !== $photo->user_id) {
            abort(404);
        }

        DB::transaction(function () use ($user, $photo, $request): void {
            $photo->items()->attach($request->item_ids, [
                'picked_up' => $user->settings->picked_up_by_default,
                'recycled' => $user->settings->recycled_by_default,
                'deposit' => $user->settings->deposit_by_default,
            ]);

            if ($request->suggestion_id) {
                $suggestion = $photo->photoSuggestions()
                    ->where('id', $request->suggestion_id)
                    ->first();

                if ($suggestion instanceof PhotoSuggestion) {
                    $suggestion->update(['is_accepted' => true]);

                    $tagIds = [];
                    if ($suggestion->brand_tag_id && $suggestion->brand_score >= 50) {
                        $tagIds[] = $suggestion->brand_tag_id;
                    }

                    if ($suggestion->content_tag_id && $suggestion->content_score >= 50) {
                        $tagIds[] = $suggestion->content_tag_id;
                    }

                    if ($tagIds !== []) {
                        $photoItem = $photo->photoItems()
                            ->where('item_id', $suggestion->item_id)
                            ->orderByDesc('id')
                            ->first();

                        if ($photoItem instanceof PhotoItem) {
                            $photoItem->tags()->syncWithoutDetaching($tagIds);
                        }
                    }
                }
            }
        });

        return response()->json();
    }

    public function update(PhotoItem $photoItem, UpdatePhotoItemRequest $request): JsonResponse
    {
        if (auth()->id() !== $photoItem->photo->user_id) {
            abort(404);
        }

        if ($request->filled('quantity')) {
            $photoItem->quantity = $request->integer('quantity');
        }

        if ($request->filled('picked_up')) {
            $photoItem->picked_up = $request->boolean('picked_up');
        }

        if ($request->filled('recycled')) {
            $photoItem->recycled = $request->boolean('recycled');
        }

        if ($request->filled('deposit')) {
            $photoItem->deposit = $request->boolean('deposit');
        }

        $photoItem->save();

        return response()->json();
    }

    public function destroy(PhotoItem $photoItem): JsonResponse
    {
        if (auth()->id() !== $photoItem->photo->user_id) {
            abort(404);
        }

        $photoItem->delete();

        return response()->json();
    }
}
