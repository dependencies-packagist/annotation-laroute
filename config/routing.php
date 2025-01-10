<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laroute Configurations
    |--------------------------------------------------------------------------
    |
    | Only automatically register routes if set to 'true'
    |
    */

    'enabled' => env('LAROUTE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Directories Configurations
    |--------------------------------------------------------------------------
    |
    | Controllers in these directories that have routing attributes
    | will automatically be registered.
    |
    | Optionally, you can specify group configuration by using key/values
    |
    */

    'directories' => [
        app_path('Http/Controllers'),
        app_path('Http/Controllers/Backend') => [
            'domain' => config('routing.domains.backend'),
            'prefix' => 'backend',
            'as' => 'backend.',
            'middleware' => 'web',
            // Only routes matching the pattern in the file are registered
            'only' => ['*Controller.php'],
            // Except routes from registration pattern matching file
            'except' => [],
        ],
        app_path('Http/Controllers/Enterprise') => [
            'domain' => config('routing.domains.enterprise'),
            'prefix' => 'enterprise',
            'as' => 'enterprise.',
            'middleware' => [
                'web',
            ],
            // Only routes matching the pattern in the file are registered
            'only' => ['*Controller.php'],
            // Except routes from registration pattern matching file
            'except' => [],
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Domains Configurations
    |--------------------------------------------------------------------------
    |
    | Route groups may also be used to handle subdomain routing.
    | Subdomains may be assigned route parameters just like route URIs,
    | allowing you to capture a portion of the subdomain for usage in your route or controller.
    |
    */

    'domains' => [
        'backend' => env('LAROUTE_DOMAINS_BACKEND', 'backend.lvh.me'),
        'enterprise' => env('LAROUTE_DOMAINS_ENTERPRISE', 'enterprise.lvh.me'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware Configurations
    |--------------------------------------------------------------------------
    |
    | This middleware will be applied to all routes.
    |
    */

    'middleware' => [
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | ScopeBindings Configurations
    |--------------------------------------------------------------------------
    |
    | When enabled, implicitly scoped bindings will be enabled by default.
    | You can override this behaviour by using the `ScopeBindings` attribute, and passing `false` to it.
    |
    | Possible values:
    |  - null: use the default behaviour
    |  - true: enable implicitly scoped bindings for all routes
    |  - false: disable implicitly scoped bindings for all routes
    |
    */

    'scope-bindings' => env('LAROUTE_SCOPE_BINDINGS'),

];
