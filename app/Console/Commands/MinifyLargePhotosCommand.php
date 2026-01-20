<?php

namespace App\Console\Commands;

use App\Jobs\MinifyPhoto;
use App\Models\Photo;
use Illuminate\Console\Command;

class MinifyLargePhotosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:minify-large-photos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch jobs to minify photos without size_kb or size over 300kb';

    public function handle(): void
    {
        $query = Photo::query()
            ->whereNull('size_kb')
            ->orWhere('size_kb', '>', 300);

        $count = $query->count();

        if ($count === 0) {
            $this->components->info('No photos found for minification.');

            return;
        }

        $this->components->info("Dispatching {$count} photos for minification...");

        $bar = $this->output->createProgressBar($count);
        $bar->setFormat('debug');
        $bar->start();

        $query->lazyById()->each(function (Photo $photo) use ($bar): void {
            MinifyPhoto::dispatch($photo);

            $bar->advance();
        });

        $bar->finish();

        $this->components->info('All photos dispatched successfully.');
    }
}
