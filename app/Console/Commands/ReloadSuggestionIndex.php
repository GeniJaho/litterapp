<?php

namespace App\Console\Commands;

use App\Actions\AI\GetLitterBotUrlAction;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ReloadSuggestionIndex extends Command
{
    protected $signature = 'ml:reload-index';

    protected $description = 'Reload the kNN FAISS index on the suggestion API without restarting the server';

    public function handle(GetLitterBotUrlAction $getLitterBotUrl): int
    {
        $url = $getLitterBotUrl->run();

        $this->components->info("Reloading suggestion index at {$url}/reload...");

        try {
            $response = Http::timeout(30)->post("{$url}/reload");
        } catch (ConnectionException $connectionException) {
            $this->components->error("Connection failed: {$connectionException->getMessage()}");

            return self::FAILURE;
        }

        if ($response->failed()) {
            $this->components->error("Reload failed with status {$response->status()}: {$response->body()}");

            return self::FAILURE;
        }

        /** @var array{status: string, index_size: int, previous_size: int} $data */
        $data = $response->json();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Status', $data['status']],
                ['Previous index size', number_format($data['previous_size'])],
                ['New index size', number_format($data['index_size'])],
            ]
        );

        $this->components->success('Suggestion index reloaded.');

        return self::SUCCESS;
    }
}
