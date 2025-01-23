<?php

namespace Annotation\Routing\Contracts;

use Closure;
use Illuminate\Routing\Route;

interface GateWayRouteContract
{
    public function gateWay(string $endpoint = 'gateway.do', Closure $action = null, Closure $version = null): Route;

}
