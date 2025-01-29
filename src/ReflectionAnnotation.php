<?php

namespace Annotation\Routing;

use Annotation\Routing\Attributes\Contracts\RouteAttributeContract;
use Annotation\Routing\Attributes\Contracts\RouteResourceAttributeContract;
use Annotation\Routing\Attributes\Route;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionAttribute;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Reflective\Reflection\ReflectionClass;

class ReflectionAnnotation
{
    protected array $methodsStack = [];
    protected array $parentsStack = [];

    public function __construct(
        protected string                   $uri,
        protected string                   $prefix,
        protected readonly ReflectionClass $reflectionClass,
        protected array                    $attributes,
        protected array                    $middleware,
    )
    {
        //
    }

    /**
     * @param \ReflectionClass|ReflectionMethod $class
     * @param string|null                       $name
     *
     * @return Route[]
     */
    protected function getAttributesInstance(\ReflectionClass|ReflectionMethod $class, string $name = null): array
    {
        return array_map(
            fn(ReflectionAttribute $attribute) => $attribute->newInstance(),
            $class->getAttributes($name, ReflectionAttribute::IS_INSTANCEOF)
        );
    }

    private function getParameters(ReflectionMethod $method): Collection
    {
        return collect($method->getParameters())
            ->filter(fn(ReflectionParameter $parameter) => is_null($parameter->getType()) || $parameter->getType() instanceof ReflectionNamedType)
            ->filter(function (ReflectionParameter $parameter) {
                if ($parameter->getType() instanceof ReflectionNamedType) {
                    return $parameter->getType()->isBuiltin() || is_a($parameter->getType()->getName(), Model::class, true);
                }
                return true;
            })
            ->reject(fn(ReflectionParameter $parameter) => is_a($parameter->getType()?->getName(), Request::class, true) || $parameter->isVariadic())
            ->mapWithKeys(function (ReflectionParameter $parameter) {
                $name        = $parameter->getName();
                $isAvailable = $parameter->isDefaultValueAvailable();
                $key         = $parameter->isOptional() ? "{{$name}?}" : "{{$name}}";
                return [
                    $key => $isAvailable ? [$name => $parameter->getDefaultValue()] : [],
                ];
            });
    }

    private function prependParentRoute(array $routes): array
    {
        array_unshift($routes, new Route(
            methods: $this->getAttribute('methods', $this->getDefaultMethods()),
            name: $this->getAttribute('as', $this->getPrefix()),
            controller: $this->getClassName(),
            namespace: $this->getNamespace(),
            prefix: $this->getAttribute('prefix', $this->getPrefix()),
            domain: $this->getAttribute('domain'),
        ));
        return $routes;
    }

    private function prependCurrentRoute(array $routes): array
    {
        array_unshift($routes, new Route(
            name: $this->getUri(),
            prefix: $this->getUri(),
        ));
        return $routes;
    }

    private function prependMethodRoute(array $routes, ReflectionMethod $method): array
    {
        $name      = $method->getName();
        $parameter = $this->getParameters($method);
        array_unshift($routes, new Route(
            name: Str::kebab($name),
            uri: trim(Str::kebab($name) . '/' . $parameter->keys()->implode('/'), '/'),
            action: $name,
            wheres: $parameter->values()->flatMap(fn($v) => $v)->all(),
        ));
        return $routes;
    }

    protected function getResourceMethodAttributes(): static
    {
        $methods            = array_map(
            fn(ReflectionMethod $method) => $method->getName(),
            $this->reflectionClass->getDeclaredMethods(ReflectionMethod::IS_PUBLIC)
        );
        $this->methodsStack = $this->methodsStack ?: collect($this->getAttributesInstance($this->reflectionClass, RouteResourceAttributeContract::class))
            ->reduce(function (array $routes, RouteResourceAttributeContract $resource) use ($methods) {
                $methods = array_intersect($resource->getResourceMethods(), $methods);
                foreach ($methods as $method) {
                    $routes[$method] = [
                        new Route(
                            methods: $resource->getMethods($method),
                            name: $resource->getName($this->getUri(), $method),
                            uri: $resource->getUri($this->getUri(), $method),
                            action: $method,
                            middleware: $resource->getMiddleware(),
                            withoutMiddleware: $resource->getWithoutMiddleware(),
                            defaults: $resource->getDefaults(),
                            wheres: $resource->getWheres(),
                            bindingFields: $resource->getBindingFields(),
                            lockSeconds: $resource->getLockSeconds(),
                            waitSeconds: $resource->getWaitSeconds(),
                            withTrashed: $resource->isWithTrashed(),
                        ),
                    ];
                }
                return $routes;
            }, []);
        return $this;
    }

    protected function getMethodAttributes(): static
    {
        $this->methodsStack = $this->methodsStack ?: collect($this->reflectionClass->getDeclaredMethods(ReflectionMethod::IS_PUBLIC))
            ->mapWithKeys(function (ReflectionMethod $method) {
                $routes = $this->getAttributesInstance($method, RouteAttributeContract::class);
                return [
                    $method->getName() => $this->prependMethodRoute($routes, $method),
                ];
            })
            ->all();
        return $this;
    }

    public function resolving(): static
    {
        $parentsStack       = collect($this->reflectionClass->getParentClasses());
        $this->parentsStack = with($parentsStack, function (Collection $collection) {
            return $collection
                ->transform(function (\ReflectionClass $class) {
                    return $this->getAttributesInstance($class, RouteAttributeContract::class);
                })
                ->map(function (array $routes, string $className) use ($collection) {
                    if ($className === $collection->keys()->first()) {
                        return $this->getResourceMethodAttributes()->getMethodAttributes()->prependCurrentRoute($routes);
                    }
                    if ($className === $collection->keys()->last()) {
                        return $this->prependParentRoute($routes);
                    }
                    return $routes;
                })
                ->reverse()
                ->filter()
                ->all();
        });
        return $this;
    }

    public function wip1(): array
    {
        $middleware = $this->getAttribute('middleware', []);
        return [
            'methods'    => $this->getAttribute('methods', $this->getDefaultMethods()),
            'domain'     => $this->getAttribute('domain'),
            'prefix'     => $this->getAttribute('prefix', $this->getPrefix()),
            'as'         => $this->getAttribute('as', Str::replace('\\', '.', $this->getPrefix())),
            'middleware' => array_merge($middleware, $this->getMiddleware()),
        ];
    }

    public function getRoutes(): array
    {
        return collect($this->reflectionClass->getDeclaredMethods(ReflectionMethod::IS_PUBLIC))
            ->mapWithKeys(function (ReflectionMethod $method) {
                return [$this->getRouteName($method) => $this->getRoute($method)];
            })
            ->all();
    }

    private function getRouteName(ReflectionMethod $method): string
    {
        return "{$this->getAttribute('as')}.{$this->getUri()}.{$method->getName()}";
    }

    private function getRoute(ReflectionMethod $method): array
    {
        return [
            'methods'       => $this->getDefaultMethods(),
            'uri'           => $method->getName(),
            'action'        => $this->getAction($method),
            'fallback'      => false,
            'defaults'      => [],
            'wheres'        => [],
            'bindingFields' => [],
            'lockSeconds'   => null,
            'waitSeconds'   => null,
            'withTrashed'   => false,
        ];
    }

    private function getAction(ReflectionMethod $method): array
    {
        return [
            'middleware' => array_merge($this->getAttribute('middleware', []), $this->getMiddleware()),
            'domain'     => $this->getAttribute('domain'),
            'uses'       => "{$method->getDeclaringClass()->getName()}@{$method->getName()}",
            'controller' => "{$method->getDeclaringClass()->getName()}@{$method->getName()}",
            'as'         => $this->getRouteName($method),
            'namespace'  => $method->getDeclaringClass()->getNamespaceName(),
            'prefix'     => trim("{$this->getAttribute('prefix')}/{$this->getUri()}", '/'),
            'where'      => [],
        ];
    }

    public function getDefaultMethods(): array
    {
        return config('routing.default_methods', RouteAttributeContract::GET);
    }

    /**
     * Get a randomly generated route name.
     *
     * @return string
     */
    protected function generateRouteName(): string
    {
        return 'generated::' . Str::random();
    }

    /**
     * @return array
     */
    public function getMethodsStack(): array
    {
        return $this->methodsStack;
    }

    /**
     * @return array
     */
    public function getParentsStack(): array
    {
        return $this->parentsStack;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     *
     * @return static
     */
    public function setUri(string $uri): static
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     *
     * @return static
     */
    public function setPrefix(string $prefix): static
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     *
     * @return static
     */
    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @param string     $attribute
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getAttribute(string $attribute, mixed $default = null): mixed
    {
        $value = $this->attributes[$attribute] ?? $default;
        return match ($attribute) {
            'middleware', 'methods' => is_array($value) ? $value : [$value],
            'prefix' => $this->getAttribute('domain') ? '' : trim($value ?: $this->getPrefix(), '/'),
            'as', 'name' => trim(Str::replace('/', '.', $value ?: $this->getPrefix()), '.'),
            default => $value
        };
    }

    /**
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * @param array $middleware
     *
     * @return static
     */
    public function setMiddleware(array $middleware): static
    {
        $this->middleware = $middleware;
        return $this;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->reflectionClass->getName();
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return $this->reflectionClass->getShortName();
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->reflectionClass->getNamespaceName();
    }

}
