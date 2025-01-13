<?php

namespace Annotation\Routing\Contracts;

use Closure;

interface PendingRouteContract
{
    public function discover(): static;

    public function gateWay(string $endpoint = 'gateway.do', Closure $action = null, Closure $version = null): static;

}
