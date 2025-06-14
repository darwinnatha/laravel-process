<?php

namespace DarwinNatha\Process;

use DarwinNatha\Process\Console\MakeProcessCommand;
use DarwinNatha\Process\Console\MakeTaskCommand;
use Illuminate\Support\ServiceProvider;

class ProcessServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeProcessCommand::class,
                MakeTaskCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        // Register bindings if needed
    }
}
