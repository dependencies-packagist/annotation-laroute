<?php

namespace Annotation\Routing\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'route.scan:clear')]
class RouteClearCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'route.scan:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the scanned route cache file';

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected Filesystem $files;

    /**
     * Create a new route clear command instance.
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
     */
    public function handle(): void
    {
        $this->files->delete($this->laravel->getCachedScannedRoutesPath());

        $this->components->info('Route cache cleared successfully.');
    }
}
