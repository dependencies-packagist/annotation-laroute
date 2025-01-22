<?php

namespace Annotation\Routing\Attributes;

use Annotation\Routing\Contracts\InvokeContract;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Route implements Contracts\RouteAttributeContract
{
    protected bool $fallback = false;

    public function __construct(
        protected array                      $methods = [],
        protected string|null                $name = null,
        protected string|null                $uri = null,
        protected string|null                $action = null,
        protected string|null                $controller = null,
        protected string|null                $namespace = null,
        protected string|null|InvokeContract $uses = null,
        protected string|null                $prefix = null,
        protected string|null                $domain = null,
        protected array                      $middleware = [],
        protected array                      $withoutMiddleware = [],
        protected array                      $defaults = [],
        protected array                      $wheres = [],
        protected array                      $bindingFields = [],
        protected int|null                   $lockSeconds = null,
        protected int|null                   $waitSeconds = null,
        protected bool                       $withTrashed = false,
        protected string                     $version = '1.0.0',
    )
    {
        $this->boot();
    }

    protected function boot(): void
    {

    }

}
