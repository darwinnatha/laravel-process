<?php

namespace DarwinNatha\Process\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeProcessCommand extends Command implements Isolatable
{
    protected $signature = 'make:process {name?} {--group=} {--force}';
    protected $description = 'Create a new process class';

    public function handle(): mixed
    {
        $name = $this->argument('name') ?? $this->ask('Nom de la classe de Process (ex: LoginProcess)');
        $group = $this->option('group');

        // Extraire le nom de classe du chemin complet si nécessaire
        $className = $this->extractClassName($name);
        
        // Si pas de groupe spécifié, utiliser directement le dossier Processes
        if (empty($group)) {
            $namespace = 'App\\Processes';
            $path = app_path("Processes/$className.php");
        } else {
            $formattedGroup = $this->formatGroupPath($group);
            $namespace = 'App\\Processes\\' . str_replace('/', '\\', $formattedGroup);
            $path = app_path('Processes/' . $formattedGroup . "/$className.php");
        }
        
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
                $this->info("Aborted. Not Modified.");
                return self::SUCCESS;
            }
        }

        file_put_contents($path, $content);
        
        $this->info("✅ Process [$className] created successfully in [$path]");
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

    protected function extractClassName(string $name): string
    {
        // Si le nom contient des slashes, prendre seulement la dernière partie
        if (str_contains($name, '/')) {
            $parts = explode('/', $name);
            $className = end($parts);
        } else {
            $className = $name;
        }
        
        return Str::studly($className);
    }

}

