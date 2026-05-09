<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

it('reloads the suggestion index successfully', function (): void {
    Http::fake([
        '*/reload' => Http::response([
            'status' => 'reloaded',
            'index_size' => 5000,
            'previous_size' => 4500,
        ]),
    ]);

    $this->artisan('ml:reload-index')
        ->expectsOutputToContain('Reloading suggestion index')
        ->expectsOutputToContain('Suggestion index reloaded')
        ->assertSuccessful();
});

it('handles a failed response from the suggestion API', function (): void {
    Http::fake([
        '*/reload' => Http::response('Internal Server Error', 500),
    ]);

    $this->artisan('ml:reload-index')
        ->expectsOutputToContain('Reload failed')
        ->assertFailed();
});

it('handles a connection error', function (): void {
    Http::fake([
        '*/reload' => fn () => throw new ConnectionException('Connection refused'),
    ]);

    $this->artisan('ml:reload-index')
        ->expectsOutputToContain('Connection failed')
        ->assertFailed();
});
