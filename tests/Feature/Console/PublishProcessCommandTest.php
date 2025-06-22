<?php

namespace DarwinNatha\Process\Tests\Feature\Console;

use DarwinNatha\Process\Tests\TestCase;
use Illuminate\Support\Facades\File;

class PublishProcessCommandTest extends TestCase
{
    public function test_it_publishes_abstract_process()
    {
        $targetPath = app_path('Processes/AbstractProcess.php');
        
        // S'assurer que le fichier n'existe pas
        File::delete($targetPath);
        
        $this->artisan('process:publish')
            ->expectsOutput('AbstractProcess published successfully!')
            ->assertExitCode(0);
        
        $this->assertTrue(File::exists($targetPath));
        
        $content = File::get($targetPath);
        $this->assertStringContainsString('namespace App\Processes;', $content);
        $this->assertStringContainsString('abstract class AbstractProcess', $content);
        $this->assertStringContainsString('public function handle(mixed $input): mixed', $content);
        $this->assertStringContainsString('You can customize this method', $content);
        
        // Nettoyage
        File::delete($targetPath);
    }

    public function test_it_asks_before_overwriting_existing_file()
    {
        $targetPath = app_path('Processes/AbstractProcess.php');
        
        // Créer un fichier existant
        File::ensureDirectoryExists(dirname($targetPath));
        File::put($targetPath, '<?php // existing file');
        
        $this->artisan('process:publish')
            ->expectsQuestion('AbstractProcess already exists. Overwrite?', false)
            ->expectsOutput('Publication cancelled.')
            ->assertExitCode(0);
        
        $content = File::get($targetPath);
        $this->assertEquals('<?php // existing file', $content);
        
        // Nettoyage
        File::delete($targetPath);
    }

    public function test_it_overwrites_with_force()
    {
        $targetPath = app_path('Processes/AbstractProcess.php');
        
        // Créer un fichier existant
        File::ensureDirectoryExists(dirname($targetPath));
        File::put($targetPath, '<?php // existing file');
        
        $this->artisan('process:publish --force')
            ->expectsOutput('AbstractProcess published successfully!')
            ->assertExitCode(0);
        
        $content = File::get($targetPath);
        $this->assertStringContainsString('namespace App\Processes;', $content);
        $this->assertStringNotContainsString('// existing file', $content);
        
        // Nettoyage
        File::delete($targetPath);
    }
}
