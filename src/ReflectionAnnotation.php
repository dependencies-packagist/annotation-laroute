<?php

namespace Annotation\Routing;

use Annotation\Routing\Attributes\Contracts\RouteAttributeContract;
use Annotation\Routing\Attributes\Contracts\RouteFallbackAttributeContract;
use Annotation\Routing\Attributes\Contracts\RouteResourceAttributeContract;
use Annotation\Routing\Attributes\ResourceAttribute;
use Annotation\Routing\Attributes\Route;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
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
     * @param                                   $name
     *
     * @return Route[]
     */
    protected function getRouteAttributes(\ReflectionClass|ReflectionMethod $class, $name): array
    {
        return array_map(
            fn(ReflectionAttribute $attribute) => $attribute->newInstance(),
            $class->getAttributes($name, ReflectionAttribute::IS_INSTANCEOF)
        );
    }

    private function resolvingResourceMethodAttributes(ReflectionAttribute $attribute): array
    {
        $route = $attribute->newInstance();
        return collect(with($route, fn(ResourceAttribute $route) => $route->getResourceMethods()))
            ->map(function (string $method) use ($route) {
                return new Route(
                    methods: $route->getMethods($method),
                    name: Str::kebab($route->getName($method)),
                    uri: Str::kebab($method),
                    action: $method,
                    controller: $this->reflectionClass->getName(),
                    namespace: $this->reflectionClass->getNamespaceName(),
                    uses: "{$this->reflectionClass->getName()}@{$method}",
                    prefix: "{$this->getPrefix()}/{$this->getUri()}",
                );
            })
            ->all();
    }

    private function resolvingMethodAttributes(ReflectionMethod $method): array
    {
        $defaults = [];
        $uri      = collect($method->getParameters())
            ->filter(fn(ReflectionParameter $parameter) => is_null($parameter->getType()) || $parameter->getType() instanceof ReflectionNamedType)
            ->filter(function (ReflectionParameter $parameter) {
                if ($parameter->getType() instanceof ReflectionNamedType) {
                    return $parameter->getType()->isBuiltin() || is_a($parameter->getType()->getName(), Model::class, true);
                }
                return true;
            })
            ->reject(fn(ReflectionParameter $parameter) => is_a($parameter->getType()?->getName(), Request::class, true) || $parameter->isVariadic())
            ->map(function (ReflectionParameter $parameter) use (&$defaults) {
                if ($parameter->isDefaultValueAvailable()) {
                    $defaults[$parameter->getName()] = $parameter->getDefaultValue();
                }
                return sprintf($parameter->isOptional() ? '{%s?}' : '{%s}', $parameter->getName());
            })
            ->implode('/');
        return [
            new Route(
                name: Str::kebab($method->getName()),
                uri: $uri ?: Str::kebab($method->getName()),
                action: $method->getName(),
                controller: $method->getDeclaringClass()->getName(),
                namespace: $method->getDeclaringClass()->getNamespaceName(),
                uses: "{$method->getDeclaringClass()->getName()}@{$method->getName()}",
                prefix: "{$this->getPrefix()}/{$this->getUri()}",
                defaults: $defaults
            ),
        ];
    }

    private function resolvingRootAttributes(): array
    {
        return [
            $this->getNamespace() => new Route(
                name: Str::replace('\\', '', $this->getPrefix()),
                prefix: $this->getPrefix(),
            ),
            $this->getClassName() => new Route(
                name: $this->getUri(),
                controller: $this->getClassName(),
                namespace: $this->getNamespace(),
                prefix: $this->getUri(),
            ),
        ];
    }

    protected function getResourceMethodAttributes(): array
    {
        return collect($this->reflectionClass->getAttributes(RouteResourceAttributeContract::class, ReflectionAttribute::IS_INSTANCEOF))
            ->mapWithKeys(function (ReflectionAttribute $attribute) {
                return $this->resolvingResourceMethodAttributes($attribute);
            })
            ->all();
    }

    protected function getMethodAttributes(): array
    {
        return collect($this->reflectionClass->getDeclaredMethods(ReflectionMethod::IS_PUBLIC))
            ->mapWithKeys(function (ReflectionMethod $method) {
                $attributes = $this->getRouteAttributes($method, RouteAttributeContract::class);
                return [
                    $method->getName() => count($attributes) ? $attributes : $this->resolvingMethodAttributes($method),
                ];
            })
            ->filter()
            ->all();
    }

    protected function getParentAttributes(): array
    {
        return collect($this->reflectionClass->getParentClasses())
            ->map(fn(\ReflectionClass $class) => $this->getRouteAttributes($class, RouteAttributeContract::class))
            ->filter()
            ->all();
    }

    private function hasRootAttribute(): bool
    {
        return collect($this->reflectionClass->getAttributes(RouteAttributeContract::class, ReflectionAttribute::IS_INSTANCEOF))
            ->reject(fn(\ReflectionAttribute $class) => is_subclass_of($class->getName(), RouteFallbackAttributeContract::class))
            ->isNotEmpty();
    }

    protected function bootstrap(): static
    {
        $this->parentsStack = $this->hasRootAttribute() ? $this->getParentAttributes() : $this->resolvingRootAttributes();
        $this->methodsStack = $this->getResourceMethodAttributes() ?: $this->getMethodAttributes();
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

    public function wip2(): array
    {
        return [
            'controller' => $this->getClassName(),
            'namespace'  => $this->getNamespace(),
            'prefix'     => $this->getUri(),
            'as'         => $this->getUri(),
        ];

    }

    public function getRoutes(): array
    {
        return collect($this->reflectionClass->getDeclaredMethods(ReflectionMethod::IS_PUBLIC))
            ->mapWithKeys(function (ReflectionMethod $method) {
                $name = "{$this->getAttribute('as')}.{$this->getUri()}.{$method->getName()}";
                return [
                    $name => [
                        'methods'       => $this->getDefaultMethods(),
                        'uri'           => $method->getName(),
                        'action'        => [
                            'middleware' => array_merge($this->getAttribute('middleware', []), $this->getMiddleware()),
                            'domain'     => $this->getAttribute('domain'),
                            'uses'       => "{$method->getDeclaringClass()->getName()}@{$method->getName()}",
                            'controller' => "{$method->getDeclaringClass()->getName()}@{$method->getName()}",
                            'as'         => $name,
                            'namespace'  => $method->getDeclaringClass()->getNamespaceName(),
                            'prefix'     => trim("{$this->getAttribute('prefix')}/{$this->getUri()}", '/'),
                            'where'      => [],
                        ],
                        'fallback'      => false,
                        'defaults'      => [],
                        'wheres'        => [],
                        'bindingFields' => [],
                        'lockSeconds'   => null,
                        'waitSeconds'   => null,
                        'withTrashed'   => false,
                    ],
                ];
            })
            ->all();
    }

    public function getDefaultMethods(): array
    {
        return config('routing.default_methods', Route::GET);
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
            'prefix' => $this->getAttribute('domain') ? '' : (trim($value, '/') ?: $this->getPrefix()),
            'as', 'name' => trim($value, '.') ?: Str::replace('/', '.', $this->getPrefix()),
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
