<?php

namespace Annotation\Routing\Facades;

use Annotation\Routing\Contracts\GateWayRouteContract;
use Annotation\Routing\Contracts\PendingRouteContract;
use Annotation\Routing\GateWayRoute;
use Annotation\Routing\PendingRoute;
use Closure;
use Illuminate\Support\Facades\Facade;

/**
 * @method static PendingRoute discover(Closure $callback = null)
 * @method static \Illuminate\Routing\Route gateWay(string $endpoint = 'gateway.do', Closure $action = null, Closure $version = null)
 *
 * @see PendingRouteContract
 * @see PendingRoute
 * @see GateWayRouteContract
 * @see GateWayRoute
 */
class Route extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'router';
    }

}
