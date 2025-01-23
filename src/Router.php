<?php

namespace Annotation\Routing;

use Annotation\Routing\Contracts\GateWayRouteContract;
use Annotation\Routing\Contracts\PendingRouteContract;
use Closure;
use Illuminate\Routing\Route;

class Router
{
    /**
     * Discover method that returns a Closure which resolves a PendingRouteContract instance.
     *
     * @return Closure
     */
    public function discover(): Closure
    {
        return function (Closure $handle = null): PendingRouteContract {
            return app(PendingRouteContract::class)->discover($handle);
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
        return function (string $endpoint = 'gateway.do', Closure $action = null, Closure $version = null): Route {
            return app(GateWayRouteContract::class)->gateWay($endpoint, $action, $version);
        };
    }

}
