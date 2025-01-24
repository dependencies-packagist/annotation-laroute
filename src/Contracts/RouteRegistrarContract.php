<?php

namespace Annotation\Routing\Contracts;

use Illuminate\Support\Collection;

interface RouteRegistrarContract
{
    /**
     * Get Scanned Routes
     *
     * @return Collection
     */
    public function getRoutes(): Collection;

    /**
     * Get the scan routing path
     *
     * @return Collection
     */
    public function getDirectories(): Collection;

    /**
     * Set the scan routing path
     *
     * @param array $directories
     *
     * @return static
     */
    public function setDirectories(array $directories): static;

    /**
     * Get the global routing middleware
     *
     * @return array
     */
    public function getMiddleware(): array;

    /**
     * Set the global routing middleware
     *
     * @param array $middleware
     *
     * @return static
     */
    public function setMiddleware(array $middleware): static;

}
