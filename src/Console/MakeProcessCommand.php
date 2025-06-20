<?php

namespace DarwinNatha\Process\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class MakeProcessCommand extends Command implements Isolatable
{
    protected $signature = 'make:process {name? : Le nom de la classe ou le chemin complet (ex: Auth/LoginProcess)} {--group= : Groupe optionnel (remplacé par le chemin si fourni)} {--force : Force la création même si le fichier existe}';
    protected $description = 'Create a new process class. Supports path notation: Auth/LoginProcess creates LoginProcess in Auth namespace';

    public function handle(): mixed
    {
        $name = $this->argument('name') ?? $this->ask('Nom de la classe de Process (ex: LoginProcess ou Auth/LoginProcess)');
        $group = $this->option('group');

        // Si le nom contient des slashes, on extrait le groupe automatiquement
        if (str_contains($name, '/')) {
            $parts = explode('/', trim($name, '/'));
            $className = array_pop($parts); // Dernière partie = nom de classe
            $detectedGroup = implode('/', $parts); // Le reste = groupe
            
            // Si --group est fourni, il a priorité, sinon on utilise le groupe détecté
            $finalGroup = $group ?? $detectedGroup;
        } else {
            $className = $name;
            $finalGroup = $group;
        }
        
        $className = Str::studly($className);
        
        // Si pas de groupe final, créer directement dans Processes
        if (empty($finalGroup)) {
            $namespace = 'App\\Processes';
            $path = app_path("Processes/$className.php");
            $relativePath = "app/Processes/$className.php";
        } else {
            $formattedGroup = $this->formatGroupPath($finalGroup);
            $namespace = 'App\\Processes\\' . str_replace('/', '\\', $formattedGroup);
            $path = app_path('Processes/' . $formattedGroup . "/$className.php");
            $relativePath = "app/Processes/$formattedGroup/$className.php";
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
        
        $this->info("✅ Process [$className] created successfully at: $relativePath");
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

