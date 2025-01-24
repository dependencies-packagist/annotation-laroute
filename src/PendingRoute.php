<?php

namespace Annotation\Routing;

use Annotation\Routing\Contracts\PendingRouteContract;
use Annotation\Routing\Contracts\RouteRegistrarContract;
use Closure;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Contracts\Pipeline\Pipeline as PipelineContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class PendingRoute implements PendingRouteContract
{
    protected PipelineContract $pipeline;
    protected array            $pipes = [];

    public function __construct(
        protected RouteRegistrarContract $route,
    )
    {
        $this->pipeline = new Pipeline;
    }

    public function pipe(Closure $callback): static
    {
        $this->pipes[] = $callback;
        return $this;
    }

    /**
     * Determines whether application routes are registered.
     *
     * @return bool
     */
    public function shouldRegisterRoutes(): bool
    {
        if (!config('routing.enabled')) {
            return false;
        }

        if (app()->routesAreCached()) {
            return false;
        }

        return true;
    }

    public function discover(Closure $callback = null): static
    {
        if (!$this->shouldRegisterRoutes()) {
            return $this;
        }

        if (app()->scannedRoutesAreCached()) {
            app()->booted(function ($app) {
                require $app->getCachedScannedRoutesPath();
            });
            return $this;
        }

        return $this->pipe($callback)->handle(fn(array $options) => $this->sendThroughRouter($options));
    }

    public function getRoutes(): Collection
    {
        return $this->route->getRoutes();
    }

    protected function handle(Closure $callback)
    {
        return tap($this, fn() => $this->getRoutes()->map($callback));
    }

    protected function sendThroughRouter(array $options)
    {
        return $this->pipeline->send($options)
            ->through($this->pipes)
            ->then(function ($destination) {
                return $this->registerRoute($destination);
            });
    }

    protected function registerRoute(array $destination): \Illuminate\Routing\Route
    {
        $route = Route::match($destination['methods'], $destination['uri'], $destination['action']);

        if ($destination['fallback']) {
            $route->fallback();
        }

        return $route;
    }

}
