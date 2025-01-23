<?php

namespace Annotation\Routing;

use Annotation\Routing\Contracts\PendingRouteContract;
use Annotation\Routing\Contracts\RouteRegistrarContract;
use Closure;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Contracts\Pipeline\Pipeline as PipelineContract;
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

    public function discover(Closure $callback = null): static
    {
        if (app()->scannedRoutesAreCached()) {
            app()->booted(function ($app) {
                require $app->getCachedScannedRoutesPath();
            });
            return $this;
        }
        $this->pipes[] = $callback;
        $this->route->loadScannedRoutes()->getRoutes()
            ->map(function (array $options, string $name) {
                return $this->pipeline->send($options)
                    ->through($this->pipes)
                    ->then(function ($destination) use ($name) {
                        return Route::match($destination['methods'], $destination['uri'], $destination['action']);
                    });
            });
        return $this;
    }

    public function pipe(Closure $callback): static
    {
        $this->pipes[] = $callback;
        return $this;
    }

}
