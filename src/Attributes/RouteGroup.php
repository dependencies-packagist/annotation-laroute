<?php

namespace Annotation\Routing\Attributes;

use Annotation\Routing\Contracts\InvokeContract;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class RouteGroup extends Route
{
    public function __construct(
        array                      $methods = [],
        ?string                    $name = null,
        ?string                    $controller = null,
        ?string                    $namespace = null,
        InvokeContract|string|null $uses = null,
        ?string                    $prefix = null,
        ?string                    $domain = null,
        array                      $middleware = [],
        array                      $withoutMiddleware = [],
        array                      $defaults = [],
        array                      $wheres = [],
        array                      $bindingFields = [],
        ?int                       $lockSeconds = null,
        ?int                       $waitSeconds = null,
        bool                       $withTrashed = false,
        string                     $version = '1.0.0'
    )
    {
        parent::__construct(
            methods: $methods,
            name: $name,
            controller: $controller,
            namespace: $namespace,
            uses: $uses,
            prefix: $prefix,
            domain: $domain,
            middleware: $middleware,
            withoutMiddleware: $withoutMiddleware,
            defaults: $defaults,
            wheres: $wheres,
            bindingFields: $bindingFields,
            lockSeconds: $lockSeconds,
            waitSeconds: $waitSeconds,
            withTrashed: $withTrashed,
            version: $version
        );
    }

}
