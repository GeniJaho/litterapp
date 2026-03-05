<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class MlBackfillConsentTrainingTimestamps extends Command
{
    protected $signature = 'ml:backfill-consent-training-timestamps';

    protected $description = 'Backfill consent_to_training_at for users that still have legacy consent_to_training=true';

    public function handle(): int
    {
        $now = now()->toIso8601String();

        $total = $this->backfillQuery()->count();

        if ($total === 0) {
            $this->components->info('No users require backfill.');

            return 0;
        }

        $updated = 0;

        $this->backfillQuery()->chunkById(500, function (Collection $users) use ($now, &$updated): void {
            foreach ($users as $user) {
                $user->settings->consent_to_training_at = $now;
                $user->save();
                $updated++;
            }
        });

        $this->components->success("Backfill completed. Updated {$updated} user(s).");

        return 0;
    }

    /**
     * @return Builder<User>
     */
    private function backfillQuery(): Builder
    {
        return User::query()
            ->where('settings->consent_to_training', true)
            ->whereNull('settings->consent_to_training_at');
    }
}
