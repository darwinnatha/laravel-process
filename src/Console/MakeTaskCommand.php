<?php

namespace DarwinNatha\Process\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeTaskCommand extends Command
{
    protected $signature = 'make:task {name?} {--group=} {--force}';
    protected $description = 'Create a new task class';

    public function handle(): int
    {
        $name = $this->argument('name') ?? $this->ask('Nom de la classe Task (ex: DetermineUser)');
        $group = $this->option('group') ?? $this->ask('Nom du groupe (ex: Auth ou Request/DO)', 'Common');

        $className = Str::studly($name);
        $namespace = 'App\\Processes\\' . str_replace('/', '\\', trim($group, '/')) . '\\Tasks';
        $path = app_path('Processes/' . $group . "/Tasks/$className.php");

        $stub = $this->getStub('process');
        $content = $this->populateStub($stub,[
            'namespace' => $namespace,
            'class' => $className
        ]);

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        if (file_exists($path) && ! $this->option('force')) {
            if (! $this->confirm("The file $className already exists. Do you want to overwrite it ?", false)) {
                $this->info("Cancelled.");
                return self::SUCCESS;
            }
        }
        
        file_put_contents($path, $content);

        $this->info("✅ Task [$className] created successfully in [$path]");
        return self::SUCCESS;
    }

    protected function formatGroupPath(string $group): string
    {
        return collect(explode('/', str_replace('\\', '/', trim($group, '/'))))
            ->map(fn ($part) => ucfirst(Str::camel($part))) // Optionnel: camelCase avant ucfirst
            ->implode('/');
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addOption('force', null, InputOption::VALUE_NONE, 'Écrase le fichier s\'il existe déjà');
    }

    protected function getStub(string $filename): string
    {
        return file_get_contents(__DIR__ . "/../stubs/{$filename}.stub");
    }

    protected function populateStub(string $stub, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $stub = str_replace("{{ $key }}", $value, $stub);
        }

        return $stub;
    }
}
