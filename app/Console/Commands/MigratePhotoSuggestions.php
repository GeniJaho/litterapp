<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigratePhotoSuggestions extends Command
{
    protected $signature = 'app:migrate-photo-suggestions';

    protected $description = 'Migrate data from photo_item_suggestions to photo_suggestions table';

    public function handle(): int
    {
        $count = DB::table('photo_item_suggestions')->count();

        if ($count === 0) {
            $this->components->info('No data to migrate.');

            return Command::SUCCESS;
        }

        $this->components->info("Migrating {$count} records...");

        DB::statement('
            INSERT INTO photo_suggestions (id, photo_id, item_id, item_score, item_count, is_accepted)
            SELECT id, photo_id, item_id, ROUND(score * 100), 0, is_accepted
            FROM photo_item_suggestions
        ');

        $this->components->info('Migration completed successfully.');

        return Command::SUCCESS;
    }
}
