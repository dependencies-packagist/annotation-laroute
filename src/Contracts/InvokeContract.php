<?php

namespace Annotation\Routing\Contracts;

use Closure;

interface InvokeContract
{
    public function __invoke(): Closure;

}
