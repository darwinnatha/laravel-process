<?php

namespace DarwinNatha\Process\Tests\Feature\Console;

use DarwinNatha\Process\Tests\TestCase;
use Illuminate\Support\Facades\File;

class NewFeaturesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Nettoyer les fichiers de test
        File::deleteDirectory(app_path('Processes'));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(app_path('Processes'));
        parent::tearDown();
    }

    public function test_process_with_path_notation()
    {
        // Test avec chemin Auth/LoginProcess
        $this->artisan('make:process Auth/LoginProcess')
            ->expectsOutput('✅ Process [LoginProcess] created successfully at: app/Processes/Auth/LoginProcess.php')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(app_path('Processes/Auth/LoginProcess.php')));
        
        // Vérifier le contenu du fichier
        $content = File::get(app_path('Processes/Auth/LoginProcess.php'));
        $this->assertStringContainsString('namespace App\Processes\Auth;', $content);
        $this->assertStringContainsString('class LoginProcess extends AbstractProcess', $content);
    }

    public function test_process_with_deep_path_notation()
    {
        // Test avec chemin profond Request/Do/SomeProcess
        $this->artisan('make:process Request/Do/SomeProcess')
            ->expectsOutput('✅ Process [SomeProcess] created successfully at: app/Processes/Request/Do/SomeProcess.php')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(app_path('Processes/Request/Do/SomeProcess.php')));
        
        // Vérifier le contenu du fichier
        $content = File::get(app_path('Processes/Request/Do/SomeProcess.php'));
        $this->assertStringContainsString('namespace App\Processes\Request\Do;', $content);
        $this->assertStringContainsString('class SomeProcess extends AbstractProcess', $content);
    }

    public function test_task_with_path_notation()
    {
        // Test avec chemin Auth/DetermineUser
        $this->artisan('make:task Auth/DetermineUser')
            ->expectsOutput('✅ Task [DetermineUser] created successfully at: app/Processes/Auth/Tasks/DetermineUser.php')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(app_path('Processes/Auth/Tasks/DetermineUser.php')));
        
        // Vérifier le contenu du fichier
        $content = File::get(app_path('Processes/Auth/Tasks/DetermineUser.php'));
        $this->assertStringContainsString('namespace App\Processes\Auth\Tasks;', $content);
        $this->assertStringContainsString('class DetermineUser implements TaskInterface', $content);
        $this->assertStringContainsString('mixed $input', $content);
    }

    public function test_process_without_group_goes_to_root()
    {
        // Test sans groupe
        $this->artisan('make:process SimpleProcess')
            ->expectsOutput('✅ Process [SimpleProcess] created successfully at: app/Processes/SimpleProcess.php')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(app_path('Processes/SimpleProcess.php')));
        
        // Vérifier le contenu du fichier
        $content = File::get(app_path('Processes/SimpleProcess.php'));
        $this->assertStringContainsString('namespace App\Processes;', $content);
        $this->assertStringContainsString('class SimpleProcess extends AbstractProcess', $content);
    }

    public function test_task_without_group_goes_to_tasks_root()
    {
        // Test sans groupe
        $this->artisan('make:task SimpleTask')
            ->expectsOutput('✅ Task [SimpleTask] created successfully at: app/Processes/Tasks/SimpleTask.php')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(app_path('Processes/Tasks/SimpleTask.php')));
        
        // Vérifier le contenu du fichier
        $content = File::get(app_path('Processes/Tasks/SimpleTask.php'));
        $this->assertStringContainsString('namespace App\Processes\Tasks;', $content);
        $this->assertStringContainsString('class SimpleTask implements TaskInterface', $content);
    }

    public function test_group_option_overrides_path_detection()
    {
        // Test où --group override la détection de chemin
        $this->artisan('make:process Auth/LoginProcess --group=Override')
            ->expectsOutput('✅ Process [LoginProcess] created successfully at: app/Processes/Override/LoginProcess.php')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(app_path('Processes/Override/LoginProcess.php')));
        
        // Vérifier le contenu du fichier
        $content = File::get(app_path('Processes/Override/LoginProcess.php'));
        $this->assertStringContainsString('namespace App\Processes\Override;', $content);    
    }
}
