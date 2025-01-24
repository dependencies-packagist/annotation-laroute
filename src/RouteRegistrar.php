<?php

namespace Annotation\Routing;

use Annotation\Routing\Contracts\RouteRegistrarContract;
use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Reflective\Reflection\ReflectionClass;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class RouteRegistrar implements RouteRegistrarContract
{
    protected Collection $directories;
    protected array      $middleware = [];

    public function __construct(
        protected Application $application,
        protected string      $rootNamespace,
        protected string      $rootPath,
    )
    {
        $this->rootNamespace = rtrim($this->rootNamespace, '\\');
        $this->rootPath      = rtrim($this->rootPath, DIRECTORY_SEPARATOR);
    }

    /**
     * Get Scanned Routes
     *
     * @param Closure|null $callback
     *
     * @return Collection
     */
    public function getRoutes(Closure $callback = null): Collection
    {
        return $this->directories->flatMap(function (array $directories) {
            ['path' => $path, 'options' => $options] = $directories;
            $attributes = Arr::except($options, ['only', 'except']);
            $only       = $options['only'] ?? [];
            $except     = $options['except'] ?? [];

            $files = with(new Finder, function (Finder $finder) use ($path, $only, $except) {
                return $finder->files()->in($path)->name($only)->notName($except)->sortByName();
            });
            return collect($files)->values()->flatMap(fn(SplFileInfo $file) => $this->resolving($file, $path, $attributes))->filter();
        })->map($callback ?? function ($route) {
            return $route;
        })->filter();
    }

    /**
     * Resolving routes
     *
     * @param SplFileInfo $file
     * @param string      $path
     * @param array       $attributes
     *
     * @return array|null
     */
    protected function resolving(SplFileInfo $file, string $path, array $attributes): ?array
    {
        if (!class_exists($className = $this->fullyQualifiedClassName($file))) {
            return null;
        }

        $reflection = new ReflectionClass($className);

        if (!$reflection->isInstantiable()) {
            return null;
        }

        $route = new ReflectionAnnotation(
            uri: $this->getUri($reflection->getName()),
            prefix: $this->getPrefix($reflection->getNamespaceName()),
            reflectionClass: $reflection,
            attributes: $attributes,
            middleware: $this->getMiddleware()
        );

        return $route->getRoutes();
    }

    /**
     * Get the fully qualified class name
     *
     * @param SplFileInfo $file
     *
     * @return string
     */
    protected function fullyQualifiedClassName(SplFileInfo $file): string
    {
        return trim(Str::replaceLast(
            search: ".{$file->getExtension()}",
            replace: '',
            subject: Str::replaceFirst($this->rootPath, $this->rootNamespace, $file->getRealPath())
        ), DIRECTORY_SEPARATOR);
    }

    protected function getUri(string $namespace): string
    {
        return Str::of(basename($namespace))
            ->replaceLast('Controller', '')
            ->kebab()
            ->toString();
    }

    protected function getPrefix(string $namespace): string
    {
        $rootControllerNamespace = config('routing.root_controller_namespace', 'App\Http\Controllers');
        return Str::of($namespace)
            ->replace($rootControllerNamespace, '')
            ->trim('\\')
            ->explode('\\')
            ->map(fn(string $value) => Str::kebab($value))
            ->implode('/');
    }

    /**
     * Get the scan routing path
     *
     * @return Collection
     */
    public function getDirectories(): Collection
    {
        return $this->directories;
    }

    /**
     * Set the scan routing path
     *
     * @param array $directories
     *
     * @return static
     */
    public function setDirectories(array $directories): static
    {
        $this->directories = collect($directories)->flatMap(function (array|string $options, string $path) {
            //[app_path('Http/Controllers'),]
            if (is_string($options)) {
                [$path, $options] = [$options, []];
            }
            //[app_path('Http/Controllers') => []] || [app_path('Http/Controllers') => [...]]
            if (count($options) === 0 || array_is_list($options) === false) {
                $options = [$options];
            }
            //[app_path('Http/Controllers') => [[]]]
            return collect($options)->map(function ($options) use ($path) {
                return compact('path', 'options');
            });
        });
        return $this;
    }

    /**
     * Get the global routing middleware
     *
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Set the global routing middleware
     *
     * @param array $middleware
     *
     * @return static
     */
    public function setMiddleware(array $middleware): static
    {
        $this->middleware = $middleware;
        return $this;
    }

}
