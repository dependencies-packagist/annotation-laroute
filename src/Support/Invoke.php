<?php

namespace Annotation\Routing\Support;

use Annotation\Routing\Contracts\InvokeContract;
use Closure;

abstract class Invoke implements InvokeContract
{
    protected array $options = [];

    public function __construct(...$options)
    {
        $this->options = $options;
    }

    abstract public function __invoke(): Closure;

}
