<?php

namespace App\Console\Commands;

use App\Actions\TagShortcuts\CopyUserTagShortcutsAction;
use App\Models\User;
use Illuminate\Console\Command;

class CopyUserTagShortcutsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:copy-user-tag-shortcuts-command {fromUserId} {toUserId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copies all tag shortcuts from one user to another user';

    /**
     * Execute the console command.
     */
    public function handle(CopyUserTagShortcutsAction $action): void
    {
        $fromUser = User::findOrFail($this->argument('fromUserId'));
        $toUser = User::findOrFail($this->argument('toUserId'));

        $this->components->info("Copying all tag shortcuts from [{$fromUser->name}] to [{$toUser->name}]");

        $result = $action->run($fromUser, $toUser);

        $this->components->info("Copied {$result['copied']} tag shortcuts, skipped {$result['skipped']} (already existed)");
    }
}
