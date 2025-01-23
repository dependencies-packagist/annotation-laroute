<?php

namespace Annotation\Routing;

use Annotation\Routing\Contracts\GateWayRouteContract;
use Annotation\Routing\Contracts\PendingRouteContract;
use Annotation\Routing\Contracts\RouteRegistrarContract;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use ReflectionException;

class RoutingServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/routing.php', 'routing');

        $this->app->singleton(RouteRegistrarContract::class, function ($app) {
            return tap(new RouteRegistrar(
                application: $app,
                rootNamespace: $this->getRouteRootNamespace(),
                rootPath: $this->getRouteRootPath(),
            ), function (RouteRegistrar $route) {
                $route
                    ->setDirectories($this->getRouteDirectories())
                    ->setMiddleware($this->getRouteMiddleware());
            });
        });
        $this->app->singleton(PendingRouteContract::class, function ($app) {
            return new PendingRoute($app->make(RouteRegistrarContract::class));
        });
        $this->app->singleton(GateWayRouteContract::class, function () {
            return new GateWayRoute();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws ReflectionException
     */
    public function boot(): void
    {
        Router::mixin(new \Annotation\Routing\Router);
        Application::mixin(new \Annotation\Routing\Application);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/routing.php' => $this->app->configPath('routing.php'),
            ], 'routing');
        }
    }

    private function getRouteDirectories(): array
    {
        return config('routing.directories') ?: [];
    }

    private function getRouteMiddleware(): array
    {
        return config('routing.middleware') ?: [];
    }

    private function getRouteRootNamespace(): string
    {
        return config('routing.root_namespace') ?: $this->app->getNamespace();
    }

    private function getRouteRootPath(): string
    {
        return config('routing.root_path') ?: $this->app->path();
    }

}
