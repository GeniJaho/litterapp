<?php

namespace App\Http\Controllers\Photos;

use App\Actions\Photos\ExportPhotosAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportPhotosController extends Controller
{
    public function __invoke(ExportPhotosAction $action): StreamedResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $photos = $action->run($user);

        return response()->streamJson(['photos' => $photos], 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="photos.json"',
        ]);
    }
}
