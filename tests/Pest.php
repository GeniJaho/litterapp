<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Pest\Arch\Expectations\Targeted;
use Pest\Arch\SingleArchExpectation;
use Pest\Arch\Support\FileLineFinder;
use PHPUnit\Architecture\Elements\ObjectDescription;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(TestCase::class, RefreshDatabase::class)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeZero', fn () => $this->toBe(0));

expect()->extend('toNotEagerLoadByDefault', fn (): SingleArchExpectation => Targeted::make(
    $this,
    fn (ObjectDescription $object): bool => $object
        ->reflectionClass
        ->getProperty('with')
        ->getDefaultValue() === [],
    'to not eager load by default',
    FileLineFinder::where(fn (string $line): bool => str_contains($line, '$with')),
));

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function admin(array $overrides = []): User
{
    return User::factory()->create([
        'email' => 'admin@litterapp.net',
        ...$overrides,
    ]);
}
