<?php

namespace App\Http\Controllers\Photos;

use App\Actions\Photos\ExportPhotosAction;
use App\Exports\PhotosCsvExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportPhotosController extends Controller
{
    public function __invoke(ExportPhotosAction $action): StreamedResponse|BinaryFileResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $photos = $action->run($user);

        // todo doesn't really work well
        if (request()->input('format') === 'csv') {
            return Excel::download(
                new PhotosCsvExport($photos),
                'photos.csv',
            );
        }

        return response()->streamJson(['photos' => $photos], 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="photos.json"',
        ]);
    }
}
