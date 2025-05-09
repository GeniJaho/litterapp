<?php

use Knuckles\Scribe\Extracting\Strategies;
use Knuckles\Scribe\Extracting\Strategies\BodyParameters\GetFromBodyParamAttribute;
use Knuckles\Scribe\Extracting\Strategies\BodyParameters\GetFromBodyParamTag;
use Knuckles\Scribe\Extracting\Strategies\Headers\GetFromHeaderAttribute;
use Knuckles\Scribe\Extracting\Strategies\Headers\GetFromHeaderTag;
use Knuckles\Scribe\Extracting\Strategies\Metadata\GetFromDocBlocks;
use Knuckles\Scribe\Extracting\Strategies\Metadata\GetFromMetadataAttributes;
use Knuckles\Scribe\Extracting\Strategies\QueryParameters\GetFromFormRequest;
use Knuckles\Scribe\Extracting\Strategies\QueryParameters\GetFromInlineValidator;
use Knuckles\Scribe\Extracting\Strategies\QueryParameters\GetFromQueryParamAttribute;
use Knuckles\Scribe\Extracting\Strategies\QueryParameters\GetFromQueryParamTag;
use Knuckles\Scribe\Extracting\Strategies\ResponseFields\GetFromResponseFieldAttribute;
use Knuckles\Scribe\Extracting\Strategies\ResponseFields\GetFromResponseFieldTag;
use Knuckles\Scribe\Extracting\Strategies\Responses\ResponseCalls;
use Knuckles\Scribe\Extracting\Strategies\Responses\UseApiResourceTags;
use Knuckles\Scribe\Extracting\Strategies\Responses\UseResponseAttributes;
use Knuckles\Scribe\Extracting\Strategies\Responses\UseResponseFileTag;
use Knuckles\Scribe\Extracting\Strategies\Responses\UseResponseTag;
use Knuckles\Scribe\Extracting\Strategies\Responses\UseTransformerTags;
use Knuckles\Scribe\Extracting\Strategies\StaticData;
use Knuckles\Scribe\Extracting\Strategies\UrlParameters\GetFromLaravelAPI;
use Knuckles\Scribe\Extracting\Strategies\UrlParameters\GetFromUrlParamAttribute;
use Knuckles\Scribe\Extracting\Strategies\UrlParameters\GetFromUrlParamTag;
use Knuckles\Scribe\Matching\RouteMatcher;

return [
    // The HTML <title> for the generated documentation. If this is empty, Scribe will infer it from config('app.name').
    'title' => null,

    // A short description of your API. Will be included in the docs webpage, Postman collection and OpenAPI spec.
    'description' => '',

    // The base URL displayed in the docs. If this is empty, Scribe will use the value of config('app.url') at generation time.
    // If you're using `laravel` type, you can set this to a dynamic string, like '{{ config("app.tenant_url") }}' to get a dynamic base URL.
    'base_url' => null,

    'routes' => [
        [
            // Routes that match these conditions will be included in the docs
            'match' => [
                // Match only routes whose paths match this pattern (use * as a wildcard to match any characters). Example: 'users/*'.
                'prefixes' => ['photo*', 'user/tag-shortcut*'],

                // Match only routes whose domains match this pattern (use * as a wildcard to match any characters). Example: 'api.*'.
                'domains' => ['*'],

                // [Dingo router only] Match only routes registered under this version. Wildcards are NOT supported.
                'versions' => ['v1'],
            ],

            // Include these routes even if they did not match the rules above.
            'include' => [
                'upload', 'my-photos', 'settings',
            ],

            // Exclude these routes even if they matched the rules above.
            'exclude' => [
                'admin.*', 'filament.*', 'telescope.*', 'horizon.*', 'debugbar.*', 'ignition.*', 'webhook*',
            ],
        ],
    ],

    // The type of documentation output to generate.
    // - "static" will generate a static HTMl page in the /public/docs folder,
    // - "laravel" will generate the documentation as a Blade view, so you can add routing and authentication.
    // - "external_static" and "external_laravel" do the same as above, but generate a basic template,
    // passing the OpenAPI spec as a URL, allowing you to easily use the docs with an external generator
    'type' => 'static',

    // See https://scribe.knuckles.wtf/laravel/reference/config#theme for supported options
    'theme' => 'elements',

    'static' => [
        // HTML documentation, assets and Postman collection will be generated to this folder.
        // Source Markdown will still be in resources/docs.
        'output_path' => 'public/api-docs',
    ],

    'laravel' => [
        // Whether to automatically create a docs endpoint for you to view your generated docs.
        // If this is false, you can still set up routing manually.
        'add_routes' => true,

        // URL path to use for the docs endpoint (if `add_routes` is true).
        // By default, `/docs` opens the HTML page, `/docs.postman` opens the Postman collection, and `/docs.openapi` the OpenAPI spec.
        'docs_url' => '/api-docs',

        // Directory within `public` in which to store CSS and JS assets.
        // By default, assets are stored in `public/vendor/scribe`.
        // If set, assets will be stored in `public/{{assets_directory}}`
        'assets_directory' => null,

        // Middleware to attach to the docs endpoint (if `add_routes` is true).
        'middleware' => [],
    ],

    'external' => [
        'html_attributes' => [],
    ],

    'try_it_out' => [
        // Add a Try It Out button to your endpoints so consumers can test endpoints right from their browser.
        // Don't forget to enable CORS headers for your endpoints.
        'enabled' => true,

        // The base URL for the API tester to use (for example, you can set this to your staging URL).
        // Leave as null to use the current app URL when generating (config("app.url")).
        'base_url' => null,

        // [Laravel Sanctum] Fetch a CSRF token before each request, and add it as an X-XSRF-TOKEN header.
        'use_csrf' => true,

        // The URL to fetch the CSRF token from (if `use_csrf` is true).
        'csrf_url' => '/sanctum/csrf-cookie',
    ],

    // How is your API authenticated? This information will be used in the displayed docs, generated examples and response calls.
    'auth' => [
        // Set this to true if ANY endpoints in your API use authentication.
        'enabled' => true,

        // Set this to true if your API should be authenticated by default. If so, you must also set `enabled` (above) to true.
        // You can then use @unauthenticated or @authenticated on individual endpoints to change their status from the default.
        'default' => true,

        // Where is the auth value meant to be sent in a request?
        // Options: query, body, basic, bearer, header (for custom header)
        'in' => 'bearer',

        // The name of the auth parameter (eg token, key, apiKey) or header (eg Authorization, Api-Key).
        'name' => 'token',

        // The value of the parameter to be used by Scribe to authenticate response calls.
        // This will NOT be included in the generated documentation. If empty, Scribe will use a random value.
        'use_value' => env('SCRIBE_AUTH_KEY'),

        // Placeholder your users will see for the auth parameter in the example requests.
        // Set this to null if you want Scribe to use a random value as placeholder instead.
        'placeholder' => '{YOUR_API_TOKEN}',

        // Any extra authentication-related info for your users. Markdown and HTML are supported.
        'extra_info' => 'You can retrieve a token by visiting your dashboard and clicking on your Profile icon and then <b>API Tokens</b> in the dropdown menu.',
    ],

    // Text to place in the "Introduction" section, right after the `description`. Markdown and HTML are supported.
    'intro_text' => <<<'INTRO'
This documentation aims to provide all the information you need to work with our API. It will get better with time... we promise!

<aside>As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).</aside>
INTRO
    ,

    // Example requests for each endpoint will be shown in each of these languages.
    // Supported options are: bash, javascript, php, python
    // To add a language of your own, see https://scribe.knuckles.wtf/laravel/advanced/example-requests
    'example_languages' => [
        'bash',
        'javascript',
    ],

    // Generate a Postman collection (v2.1.0) in addition to HTML docs.
    // For 'static' docs, the collection will be generated to public/docs/collection.json.
    // For 'laravel' docs, it will be generated to storage/app/scribe/collection.json.
    // Setting `laravel.add_routes` to true (above) will also add a route for the collection.
    'postman' => [
        'enabled' => true,

        'overrides' => [
            // 'info.version' => '2.0.0',
        ],
    ],

    // Generate an OpenAPI spec (v3.0.1) in addition to docs webpage.
    // For 'static' docs, the collection will be generated to public/docs/openapi.yaml.
    // For 'laravel' docs, it will be generated to storage/app/scribe/openapi.yaml.
    // Setting `laravel.add_routes` to true (above) will also add a route for the spec.
    'openapi' => [
        'enabled' => true,

        'overrides' => [
            // 'info.version' => '2.0.0',
        ],
    ],

    'groups' => [
        // Endpoints which don't have a @group will be placed in this default group.
        'default' => 'Endpoints',

        // By default, Scribe will sort groups alphabetically, and endpoints in the order their routes are defined.
        // You can override this by listing the groups, subgroups and endpoints here in the order you want them.
        // See https://scribe.knuckles.wtf/blog/laravel-v4#easier-sorting and https://scribe.knuckles.wtf/laravel/reference/config#order for details
        'order' => [],
    ],

    // Custom logo path. This will be used as the value of the src attribute for the <img> tag,
    // so make sure it points to an accessible URL or path. Set to false to not use a logo.
    // For example, if your logo is in public/img:
    // - 'logo' => '../img/logo.png' // for `static` type (output folder is public/docs)
    // - 'logo' => 'img/logo.png' // for `laravel` type
    'logo' => false,

    // Customize the "Last updated" value displayed in the docs by specifying tokens and formats.
    // Examples:
    // - {date:F j Y} => March 28, 2022
    // - {git:short} => Short hash of the last Git commit
    // Available tokens are `{date:<format>}` and `{git:<format>}`.
    // The format you pass to `date` will be passed to PHP's `date()` function.
    // The format you pass to `git` can be either "short" or "long".
    'last_updated' => 'Last updated: {date:F j, Y}',

    'examples' => [
        // Set this to any number (eg. 1234) to generate the same example values for parameters on each run,
        'faker_seed' => null,

        // With API resources and transformers, Scribe tries to generate example models to use in your API responses.
        // By default, Scribe will try the model's factory, and if that fails, try fetching the first from the database.
        // You can reorder or remove strategies here.
        'models_source' => ['factoryCreate', 'factoryMake', 'databaseFirst'],
    ],

    // The strategies Scribe will use to extract information about your routes at each stage.
    // If you create or install a custom strategy, add it here.
    'strategies' => [
        'metadata' => [
            GetFromDocBlocks::class,
            GetFromMetadataAttributes::class,
        ],
        'urlParameters' => [
            GetFromLaravelAPI::class,
            GetFromUrlParamAttribute::class,
            GetFromUrlParamTag::class,
        ],
        'queryParameters' => [
            GetFromFormRequest::class,
            GetFromInlineValidator::class,
            GetFromQueryParamAttribute::class,
            GetFromQueryParamTag::class,
        ],
        'headers' => [
            GetFromHeaderAttribute::class,
            GetFromHeaderTag::class,
            [
                StaticData::class,
                [
                    'data' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                ],
            ],
        ],
        'bodyParameters' => [
            Strategies\BodyParameters\GetFromFormRequest::class,
            Strategies\BodyParameters\GetFromInlineValidator::class,
            GetFromBodyParamAttribute::class,
            GetFromBodyParamTag::class,
        ],
        'responses' => [
            UseResponseAttributes::class,
            UseTransformerTags::class,
            UseApiResourceTags::class,
            UseResponseTag::class,
            UseResponseFileTag::class,
            [
                ResponseCalls::class,
                ['only' => ['GET *']],
            ],
        ],
        'responseFields' => [
            GetFromResponseFieldAttribute::class,
            GetFromResponseFieldTag::class,
        ],
    ],

    // For response calls, API resource responses and transformer responses,
    // Scribe will try to start database transactions, so no changes are persisted to your database.
    // Tell Scribe which connections should be transacted here. If you only use one db connection, you can leave this as is.
    'database_connections_to_transact' => [config('database.default')],

    'fractal' => [
        // If you are using a custom serializer with league/fractal, you can specify it here.
        'serializer' => null,
    ],

    'routeMatcher' => RouteMatcher::class,
];
