<?php

namespace Annotation\Routing;

use Annotation\Routing\Contracts\PendingRouteContract;
use Closure;

class Router
{
    /**
     * Discover method that returns a Closure which resolves a PendingRouteContract instance.
     *
     * @return Closure
     */
    public function discover(): Closure
    {
        return function (): PendingRouteContract {
            return app(PendingRouteContract::class)->discover();
        };
    }

    /**
     * Gateway method that returns a Closure which resolves a PendingRouteContract instance
     * with optional parameters for endpoint, action, and version.
     *
     * @return Closure
     */
    public function gateWay(): Closure
    {
        return function (string $endpoint = 'gateway.do', Closure $action = null, Closure $version = null): PendingRouteContract {
            return app(PendingRouteContract::class)->gateWay($endpoint, $action, $version);
        };
    }

}
