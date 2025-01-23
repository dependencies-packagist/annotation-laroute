<?php

namespace Annotation\Routing\Attributes;

use Annotation\Routing\Contracts\InvokeContract;

abstract class ResourceAttribute extends Route implements Contracts\RouteResourceAttributeContract
{
    protected array $resource = [];

    public function __construct(
        protected array|string     $only = [],
        protected array|string     $except = [],
        protected array            $names = [],
        string|null                $uri = null,
        string|null                $action = null,
        string|null                $controller = null,
        string|null                $namespace = null,
        InvokeContract|string|null $uses = null,
        string|null                $prefix = null,
        string|null                $domain = null,
        array                      $middleware = [],
        array                      $withoutMiddleware = [],
        array                      $defaults = [],
        array                      $wheres = [],
        array                      $bindingFields = [],
        int|null                   $lockSeconds = null,
        int|null                   $waitSeconds = null,
        bool                       $withTrashed = false,
        string                     $version = '1.0.0'
    )
    {
        $this->only   = is_array($this->only) ? $this->only : [$this->only];
        $this->except = is_array($this->except) ? $this->except : [$this->except];
        parent::__construct(
            uri: $uri,
            action: $action,
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

    /**
     * @param string|null $name
     *
     * @return array
     */
    #[\Override]
    public function getMethods(string $name = null): array
    {
        return $this->resource[$name] ?: [];
    }

    /**
     * @param string|null $name
     *
     * @return string|null
     */
    #[\Override]
    public function getName(string $name = null): ?string
    {
        return $this->names[$name] ?? $name;
    }

    public function getResourceMethods(): array
    {
        $methods = array_keys($this->resource);
        $only    = $this->only;
        $except  = $this->except;

        if (count($only)) {
            $methods = array_intersect($methods, $only);
        }

        if (count($except)) {
            $methods = array_diff($methods, $except);
        }

        return array_values($methods);
    }

}
