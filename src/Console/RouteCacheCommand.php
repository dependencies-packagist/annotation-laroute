<?php

namespace Annotation\Routing\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\RouteCollection;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'route.scan:cache')]
class RouteCacheCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'route.scan:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a scanned route cache file for faster route registration';

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected Filesystem $files;

    /**
     * Create a new route command instance.
     *
     * @param Filesystem $files
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->callSilent('route.scan:clear');

        $routes = $this->getFreshApplicationRoutes();

        if (count($routes) === 0) {
            $this->components->error("Your application doesn't have any routes.");
            return;
        }

        foreach ($routes as $route) {
            $route->prepareForSerialization();
        }

        $this->files->put(
            $this->laravel->getCachedScannedRoutesPath(), $this->buildRouteCacheFile($routes)
        );

        $this->components->info('Routes cached successfully.');
    }

    /**
     * Boot a fresh copy of the application and get the routes.
     *
     * @return RouteCollection
     */
    protected function getFreshApplicationRoutes(): RouteCollection
    {
        return tap($this->getFreshApplication()['router']->getRoutes(), function ($routes) {
            $routes->refreshNameLookups();
            $routes->refreshActionLookups();
        });
    }

    /**
     * Get a fresh application instance.
     *
     * @return Application
     */
    protected function getFreshApplication(): Application
    {
        return tap(require $this->laravel->bootstrapPath('app.php'), function ($app) {
            $app->make(ConsoleKernelContract::class)->bootstrap();
        });
    }

    /**
     * Build the route cache file.
     *
     * @param RouteCollection $routes
     *
     * @return string
     */
    protected function buildRouteCacheFile(RouteCollection $routes): string
    {
        $stub = $this->files->get(__DIR__ . '/stubs/routes.stub');

        return str_replace('{{routes}}', var_export($routes->compile(), true), $stub);
    }
}
