<?php

namespace App\Http\Controllers\Photos;

use App\Actions\Photos\ExportPhotosAction;
use App\Exports\PhotosCsvExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportPhotosController extends Controller
{
    public function __invoke(ExportPhotosAction $action): StreamedResponse|BinaryFileResponse|Response
    {
        /** @var User $user */
        $user = auth()->user();

        $photos = $action->run($user);

        if (request()->input('format') === 'csv') {
            return (new PhotosCsvExport($photos))->download('photos.csv', Excel::CSV);
        }

        return response()->streamJson(['photos' => $photos], 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="photos.json"',
        ]);
    }
}
