<?php

namespace Annotation\Routing\Contracts;

interface RouteRegistrarContract
{
    public function scan(): static;

}
