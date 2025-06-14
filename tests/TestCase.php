<?php

namespace DarwinNatha\Process\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use DarwinNatha\Process\ProcessServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ProcessServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        // Load Laravel default migrations if needed
    }
}
    