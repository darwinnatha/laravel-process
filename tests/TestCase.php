<?php

namespace DarwinNatha\Process\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use DarwinNatha\Process\ProcessServiceProvider;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Permettre à chaque test de définir ses propres mocks DB
        $this->setupDatabaseMocks();
    }

    /**
     * Configuration de base pour les mocks de base de données
     * Peut être override dans les tests spécifiques
     */
    protected function setupDatabaseMocks(): void
    {
        // Mock DB par défaut pour les tests simples
        DB::shouldReceive('beginTransaction')->andReturn(true)->byDefault();
        DB::shouldReceive('commit')->andReturn(true)->byDefault();
        DB::shouldReceive('rollBack')->andReturn(true)->byDefault();
    }

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