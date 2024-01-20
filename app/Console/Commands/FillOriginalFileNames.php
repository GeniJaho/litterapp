<?php

namespace App\Console\Commands;

use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FillOriginalFileNames extends Command
{
    protected $signature = 'app:fill-original-file-names';

    public function handle(): void
    {
        Photo::query()
            ->whereNull('original_file_name')
            ->orWhere('original_file_name', '')
            ->update([
                'original_file_name' => DB::raw('replace(path, "photos/", "")'),
            ]);
    }
}
