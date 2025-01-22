<?php

namespace Annotation\Routing\Attributes;

use Annotation\Routing\Contracts\InvokeContract;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Fallback extends Route implements Contracts\RouteFallbackAttributeContract
{
    public function __construct(
        string|null                $name = 'fallback',
        string|null                $action = null,
        string|null                $controller = null,
        string|null                $namespace = null,
        InvokeContract|string|null $uses = null,
        string|null                $prefix = null,
        string|null                $domain = null,
        array                      $middleware = [],
        array                      $withoutMiddleware = [],
        array                      $defaults = [],
        array                      $bindingFields = [],
        int|null                   $lockSeconds = null,
        int|null                   $waitSeconds = null,
        bool                       $withTrashed = false,
        string                     $version = '1.0.0'
    )
    {
        parent::__construct(
            methods: self::GET,
            name: $name,
            uri: '{fallbackPlaceholder}',
            action: $action,
            controller: $controller,
            namespace: $namespace,
            uses: $uses,
            prefix: $prefix,
            domain: $domain,
            middleware: $middleware,
            withoutMiddleware: $withoutMiddleware,
            defaults: $defaults,
            wheres: [
                'fallbackPlaceholder' => '.*',
            ],
            bindingFields: $bindingFields,
            lockSeconds: $lockSeconds,
            waitSeconds: $waitSeconds,
            withTrashed: $withTrashed,
            version: $version
        );
    }

    protected function boot(): void
    {
        $this->fallback = true;
    }

}
