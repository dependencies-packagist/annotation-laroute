<?php

namespace Annotation\Routing;

use Closure;

class Application
{
    /**
     * Determine if the application routes are cached.
     *
     * @return Closure
     */
    public function scannedRoutesAreCached(): Closure
    {
        return function (): bool {
            return $this['files']->exists($this->getCachedScannedRoutesPath());
        };
    }

    /**
     * Get the path to the routes cache file.
     *
     * @return Closure
     */
    public function getCachedScannedRoutesPath(): Closure
    {
        return function (): string {
            return $this->normalizeCachePath('APP_SCANNED_ROUTES_CACHE', 'cache/routes-v7-scanned.php');
        };
    }

}
