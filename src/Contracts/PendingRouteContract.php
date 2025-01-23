<?php

namespace Annotation\Routing\Contracts;

use Closure;

interface PendingRouteContract
{
    public function discover(Closure $callback = null): static;

}
