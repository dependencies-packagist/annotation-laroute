<?php

namespace Annotation\Routing\Contracts;

use Closure;
use Illuminate\Support\Collection;

interface PendingRouteContract
{
    public function pipe(Closure $callback): static;

    public function discover(Closure $callback = null): static;

    public function getRoutes(): Collection;

}
