<?php

namespace Annotation\Routing\Attributes;

use Annotation\Routing\Contracts\InvokeContract;
use Attribute;
use Closure;

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

    /**
     * @return bool
     */
    public function isFallback(): bool
    {
        return $this->fallback;
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return trim($this->name, '.');
    }

    /**
     * @return string|null
     */
    public function getUri(): ?string
    {
        return trim($this->uri, '/');
    }

    /**
     * @return string|null
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @return string|null
     */
    public function getController(): ?string
    {
        return $this->controller;
    }

    /**
     * @return string|null
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * @return Closure|string|null
     */
    public function getUses(): Closure|string|null
    {
        if (is_string($this->uses) && class_exists($this->uses)) {
            $this->uses = new $this->uses;
        }
        if (is_subclass_of($this->uses, InvokeContract::class)) {
            return app()->call($this->uses);
        }
        if (is_string($this->getController()) && class_exists($this->getController())) {
            if (is_null($this->getAction())) {
                $this->action = '__invoke';
            }
            $this->uses = "{$this->getController()}@{$this->getAction()}";
        }
        return $this->uses;
    }

    /**
     * @return string|null
     */
    public function getPrefix(): ?string
    {
        return trim($this->prefix, '/');
    }

    /**
     * @return string|null
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * @return array
     */
    public function getWithoutMiddleware(): array
    {
        return $this->withoutMiddleware;
    }

    /**
     * @return array
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * @return array
     */
    public function getWheres(): array
    {
        return $this->wheres;
    }

    /**
     * @return array
     */
    public function getBindingFields(): array
    {
        return $this->bindingFields;
    }

    /**
     * @return int|null
     */
    public function getLockSeconds(): ?int
    {
        return $this->lockSeconds;
    }

    /**
     * @return int|null
     */
    public function getWaitSeconds(): ?int
    {
        return $this->waitSeconds;
    }

    /**
     * @return bool
     */
    public function isWithTrashed(): bool
    {
        return $this->withTrashed;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

}
