<?php

namespace Annotation\Routing\Facades;

use Annotation\Routing\Contracts\PendingRouteContract;
use Annotation\Routing\PendingRoute;
use Closure;
use Illuminate\Support\Facades\Facade;

/**
 * @method static PendingRoute discover()
 * @method static PendingRoute gateWay(string $endpoint = 'gateway.do', Closure $action = null, Closure $version = null)
 *
 * @see PendingRouteContract
 * @see PendingRoute
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
