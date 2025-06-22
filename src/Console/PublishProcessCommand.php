<?php

namespace DarwinNatha\Process\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PublishProcessCommand extends Command
{
    protected $signature = 'process:publish 
                            {--force : Overwrite existing files}';

    protected $description = 'Publish the AbstractProcess base class for customization';

    public function __construct(
        protected Filesystem $files
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $targetPath = app_path('Processes/AbstractProcess.php');
        $stubPath = __DIR__ . '/../stubs/abstract-process.stub';

        // Create directory if it doesn't exist
        $this->files->ensureDirectoryExists(dirname($targetPath));

        // Check if file exists and handle accordingly
        if ($this->files->exists($targetPath) && !$this->option('force')) {
            if (!$this->confirm('AbstractProcess already exists. Overwrite?')) {
                $this->info('Publication cancelled.');
                return self::SUCCESS;
            }
        }

        // Copy and customize the stub
        $stub = $this->files->get($stubPath);
        $content = str_replace('{{ namespace }}', 'App\\Processes', $stub);

        $this->files->put($targetPath, $content);

        $this->info('AbstractProcess published successfully!');
        $this->line('');
        $this->line('<comment>Next steps:</comment>');
        $this->line('1. Customize your AbstractProcess in: <info>app/Processes/AbstractProcess.php</info>');
        $this->line('2. Your processes will now extend from <info>App\\Processes\\AbstractProcess</info>');
        $this->line('3. You can modify the handle() method, add middleware, logging, etc.');

        return self::SUCCESS;
    }
}
