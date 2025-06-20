<?php

namespace DarwinNatha\Process\Tests\Feature\Console;

use Illuminate\Support\Facades\File;
use DarwinNatha\Process\Tests\TestCase;

class MakeProcessCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Nettoie le dossier de test
        File::deleteDirectory(app_path('Processes'));
    }

    public function test_it_creates_a_process_file()
    {
        $this->artisan('make:process', ['name' => 'RegisterProcess', '--group' => 'Auth'])
            ->expectsOutput('✅ Process [RegisterProcess] created successfully at: app/Processes/Auth/RegisterProcess.php')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(app_path('Processes/Auth/RegisterProcess.php')));
    }

    public function test_it_asks_before_overwriting_existing_process()
    {
        $path = app_path('Processes/Auth/RegisterProcess.php');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, "// existing");

        $this->artisan('make:process', ['name' => 'RegisterProcess', '--group' => 'Auth'])
            ->expectsConfirmation('The file RegisterProcess already exists. Do you want to overwrite it ?', 'no')
            ->expectsOutput('Aborted. Not Modified.')
            ->assertExitCode(0);
    }

    public function test_it_forces_overwrite_when_specified()
    {
        $path = app_path('Processes/Auth/RegisterProcess.php');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, "// existing");

        $this->artisan('make:process', ['name' => 'RegisterProcess', '--group' => 'Auth', '--force' => true])
            ->expectsOutput('✅ Process [RegisterProcess] created successfully at: app/Processes/Auth/RegisterProcess.php')
            ->assertExitCode(0);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(app_path('Processes'));
        parent::tearDown();
    }
}
