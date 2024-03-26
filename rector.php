<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector;
use Rector\CodingStyle\Rector\Closure\StaticClosureRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use RectorLaravel\Rector\ClassMethod\AddGenericReturnTypeToRelationsRector;
use RectorLaravel\Rector\ClassMethod\MigrateToSimplifiedAttributeRector;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/tests',
    ])
    ->withImportNames()
    ->withPhpSets(php82: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: false,
        naming: false,
        instanceOf: false,
        earlyReturn: false,
        strictBooleans: false,
    )
    ->withSets([
        LaravelSetList::LARAVEL_110,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_ARRAY_STR_FUNCTION_TO_STATIC_CALL,
        LaravelSetList::LARAVEL_FACADE_ALIASES_TO_FULL_NAMES,
    ])
    ->withRules([
        MigrateToSimplifiedAttributeRector::class,
        AddGenericReturnTypeToRelationsRector::class,
    ])
    ->withSkip([
        AddLiteralSeparatorToNumberRector::class,
        PostIncDecToPreIncDecRector::class,
        StaticArrowFunctionRector::class,
        StaticClosureRector::class,
        EncapsedStringsToSprintfRector::class,
        __DIR__.'/app/Http/Middleware/RedirectIfAuthenticated.php',
    ])
    ->withCache(
        cacheDirectory: './storage/rector/cache',
        cacheClass: FileCacheStorage::class,
    );
