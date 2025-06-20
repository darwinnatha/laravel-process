<?php

namespace DarwinNatha\Process\Tests\Feature\Console;

use Illuminate\Support\Facades\File;
use DarwinNatha\Process\Tests\TestCase;

class MakeTaskCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Nettoie le dossier de test
        File::deleteDirectory(app_path('Processes'));
    }

    public function test_it_creates_a_task_file()
    {
        $this->artisan('make:task', ['name' => 'LoginTask', '--group' => 'Auth'])
            ->expectsOutput('✅ Task [LoginTask] created successfully at: app/Processes/Auth/Tasks/LoginTask.php')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(app_path('Processes/Auth/Tasks/LoginTask.php')));
    }

    public function test_it_creates_directory_if_not_exists()
    {
        $this->artisan('make:task', ['name' => 'CustomTask', '--group' => 'NewGroup'])
            ->expectsOutput('✅ Task [CustomTask] created successfully at: app/Processes/NewGroup/Tasks/CustomTask.php')
            ->assertExitCode(0);

        $this->assertDirectoryExists(app_path('Processes/NewGroup/Tasks'));
    }

    public function test_it_asks_before_overwriting_existing_file()
    {
        $path = app_path('Processes/Auth/Tasks/LoginTask.php');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, "// dummy content");

        $this->artisan('make:task', ['name' => 'LoginTask', '--group' => 'Auth'])
            ->expectsConfirmation('The file LoginTask already exists. Do you want to overwrite it ?', 'no')
            ->expectsOutput('Cancelled.')
            ->assertExitCode(0);
    }

    public function test_it_overwrites_with_force()
    {
        $path = app_path('Processes/Auth/Tasks/LoginTask.php');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, "// dummy content");

        $this->artisan('make:task', ['name' => 'LoginTask', '--group' => 'Auth', '--force' => true])
            ->expectsOutput('✅ Task [LoginTask] created successfully at: app/Processes/Auth/Tasks/LoginTask.php')
            ->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        // Nettoie après les tests
        File::deleteDirectory(app_path('Processes'));
        parent::tearDown();
    }
}
