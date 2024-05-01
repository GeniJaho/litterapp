<?php

namespace App\Http\Controllers\Photos;

use App\DTO\PhotoExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportPhotosController extends Controller
{
    public function __invoke(): StreamedResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $photos = $user
            ->photos()
            ->filter($user->settings->photo_filters)
            ->with(['photoItems' => fn (Builder $q) => $q
                ->with('item:id,name')
                ->with('tags:id,name'),
            ])
            ->lazyById();

        return response()->streamDownload(function () use ($photos) {
            echo PhotoExport::collect($photos)->toJson();
        }, 'photos.json');
    }
}
