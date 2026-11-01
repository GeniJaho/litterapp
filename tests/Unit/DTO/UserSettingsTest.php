<?php

use App\DTO\UserSettings;

it('returns a valid sort direction', function (string $storedDirection, string $expectedDirection): void {
    $settings = new UserSettings(sort_direction: $storedDirection);

    expect($settings->getValidSortDirection())->toBe($expectedDirection);
})->with([
    'ascending' => ['asc', 'asc'],
    'descending' => ['desc', 'desc'],
    'unknown value falls back to descending' => ['sideways', 'desc'],
    'empty value falls back to descending' => ['', 'desc'],
]);

it('sorts descending by default', function (): void {
    expect(new UserSettings()->getValidSortDirection())->toBe('desc');
});
