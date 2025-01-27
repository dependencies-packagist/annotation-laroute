<?php

namespace Annotation\Routing\Attributes;

use Annotation\Routing\Contracts\InvokeContract;

abstract class ResourceAttribute extends Route implements Contracts\RouteResourceAttributeContract
{
    protected array $resource = [];

    public function __construct(
        string|null                $name = null,
        protected array|string     $only = [],
        protected array|string     $except = [],
        protected array            $names = [],
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
            name: $name,
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
     * @param string|null $url
     * @param string|null $name
     *
     * @return string|null
     */
    #[\Override]
    public function getName(string $url = null, string $name = null): ?string
    {
        return array_key_exists($name, $this->names) ? $this->names[$name] : ($this->name ?: $url) . '.' . $name;
    }

    /**
     * @param string|null $url
     * @param string|null $method
     *
     * @return string|null
     */
    #[\Override]
    public function getUri(string $url = null, string $method = null): ?string
    {
        $methods = explode('.', array_key_exists($method, $this->names) ? $this->names[$method] : ($this->name ?? $url));
        $uri     = $this->getNestedResourceUri($methods);
        $last    = end($methods);
        return match ($method) {
            'index', 'store' => str_replace("/{{$last}}", '', $uri),
            'create' => str_replace("/{{$last}}", '/create', $uri),
            'show', 'update', 'destroy' => $uri,
            'edit' => "{$uri}/edit",
            default => null
        };
    }

    protected function getNestedResourceUri(array $segments): string
    {
        // We will spin through the segments and create a place-holder for each of the
        // resource segments, as well as the resource itself. Then we should get an
        // entire string for the resource URI that contains all nested resources.
        return implode('/', array_map(function ($s) {
            return "{$s}/{{$s}}";
        }, $segments));
    }

    public function getNames(): array
    {
        return $this->names;
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
