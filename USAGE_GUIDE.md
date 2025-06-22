# Guide d'Utilisation - Laravel Process

Ce guide présente tous les cas d'usage possibles du package Laravel Process avec sa nouvelle approche agnostique et flexible.

---

## 📋 Table des Matières

1. [Types d'Entrée Supportés](#types-dentrée-supportés)
2. [Types de Sortie Possibles](#types-de-sortie-possibles)
3. [Exemples de Processes](#exemples-de-processes)
4. [Exemples de Tasks](#exemples-de-tasks)
5. [Cas d'Usage Avancés](#cas-dusage-avancés)
6. [Gestion d'Erreurs](#gestion-derreurs)
7. [Patterns Recommandés](#patterns-recommandés)

---

## 🔄 Types d'Entrée Supportés

### 1. Array Simple
```php
$process = new UserRegistrationProcess();

$result = $process->execute([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'secret123'
]);
```

### 2. Request Laravel
```php
// Dans un contrôleur
public function register(RegisterRequest $request)
{
    $process = new UserRegistrationProcess();
    return $process->execute($request);
}
```

### 3. Collection Laravel
```php
use Illuminate\Support\Collection;

$userData = collect([
    'users' => [
        ['name' => 'John', 'email' => 'john@test.com'],
        ['name' => 'Jane', 'email' => 'jane@test.com']
    ]
]);

$result = $process->execute($userData);
```

### 4. DTO (Data Transfer Object)
```php
class UserRegistrationDto
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?array $metadata = null
    ) {}
}

$dto = new UserRegistrationDto('John', 'john@test.com', 'secret');
$result = $process->execute($dto);
```

### 5. Eloquent Model
```php
$user = User::find(1);
$result = $process->execute($user);
```

### 6. Objet Personnalisé
```php
class CustomData
{
    public $items = [];
    public $config = [];
    
    public function addItem($item) { $this->items[] = $item; }
}

$customData = new CustomData();
$customData->addItem(['name' => 'Item 1']);

$result = $process->execute($customData);
```

---

## 📤 Types de Sortie Possibles

### 1. Boolean Simple
```php
class ValidateUserTask implements TaskInterface
{
    public function __invoke(mixed $input, callable $next): bool
    {
        // Validation logic
        return $isValid;
    }
}
```

### 2. Array/JSON
```php
class ProcessDataTask implements TaskInterface
{
    public function __invoke(mixed $input, callable $next): array
    {
        return [
            'status' => 'success',
            'data' => $processedData,
            'timestamp' => now()
        ];
    }
}
```

### 3. Eloquent Model
```php
class CreateUserTask implements TaskInterface
{
    public function __invoke(mixed $input, callable $next): User
    {
        return User::create($input);
    }
}
```

### 4. API Resource
```php
use App\Http\Resources\UserResource;

class FormatUserTask implements TaskInterface
{
    public function __invoke(mixed $input, callable $next): UserResource
    {
        return new UserResource($input);
    }
}
```

### 5. Collection
```php
class ProcessUsersTask implements TaskInterface
{
    public function __invoke(mixed $input, callable $next): Collection
    {
        return collect($input)->map(fn($user) => $this->processUser($user));
    }
}
```

### 6. DTO de Réponse
```php
class ResponseDto
{
    public function __construct(
        public bool $success,
        public mixed $data = null,
        public ?string $message = null
    ) {}
}

class CompleteProcessTask implements TaskInterface
{
    public function __invoke(mixed $input, callable $next): ResponseDto
    {
        return new ResponseDto(true, $input, 'Process completed');
    }
}
```

### 7. Null (Pour les actions sans retour)
```php
class LogActivityTask implements TaskInterface
{
    public function __invoke(mixed $input, callable $next): mixed
    {
        Log::info('Activity logged', ['data' => $input]);
        return $next($input); // Continue le pipeline
    }
}
```

---

## 🏗️ Exemples de Processes

### Process de Création d'Utilisateur
```php
namespace App\Processes\User;

use DarwinNatha\Process\AbstractProcess;

class CreateUserProcess extends AbstractProcess
{
    public array $tasks = [
        Tasks\ValidateUserData::class,
        Tasks\CheckEmailUniqueness::class,
        Tasks\HashPassword::class,
        Tasks\CreateUserRecord::class,
        Tasks\SendWelcomeEmail::class,
        Tasks\LogUserCreation::class,
    ];
}
```

### Process de Commande E-commerce
```php
namespace App\Processes\Order;

class ProcessOrderProcess extends AbstractProcess
{
    public array $tasks = [
        Tasks\ValidateOrderData::class,
        Tasks\CheckInventory::class,
        Tasks\CalculateTotal::class,
        Tasks\ProcessPayment::class,
        Tasks\CreateOrderRecord::class,
        Tasks\UpdateInventory::class,
        Tasks\SendConfirmationEmail::class,
    ];
}
```

### Process de Traitement de Fichier
```php
namespace App\Processes\File;

class ProcessFileProcess extends AbstractProcess
{
    public array $tasks = [
        Tasks\ValidateFileFormat::class,
        Tasks\ScanForViruses::class,
        Tasks\OptimizeFile::class,
        Tasks\SaveToStorage::class,
        Tasks\CreateFileRecord::class,
        Tasks\GenerateThumbnail::class,
    ];
}
```

---

## 🔧 Exemples de Tasks

### Task avec Gestion Multi-Type
```php
namespace App\Processes\User\Tasks;

use DarwinNatha\Process\Contracts\TaskInterface;
use Illuminate\Http\Request;

class ValidateUserData implements TaskInterface
{
    public function __invoke(mixed $input, callable $next): mixed
    {
        $data = $this->extractData($input);
        
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $next($input);
    }
    
    private function extractData(mixed $input): array
    {
        return match(true) {
            $input instanceof Request => $input->all(),
            is_array($input) => $input,
            is_object($input) && method_exists($input, 'toArray') => $input->toArray(),
            is_object($input) => (array) $input,
            default => throw new InvalidArgumentException('Unsupported input type')
        };
    }
}
```

### Task avec Formatage API Optionnel
```php
namespace App\Processes\User\Tasks;

use DarwinNatha\Process\Contracts\TaskInterface;
use DarwinNatha\Process\Traits\FormatsApiResponse;

class CreateUserRecord implements TaskInterface
{
    use FormatsApiResponse; // Optionnel !

    public function __invoke(mixed $input, callable $next): mixed
    {
        try {
            $userData = $this->extractUserData($input);
            $user = User::create($userData);
            
            // Avec formatage API (optionnel)
            return $this->successResponse($user, 'User created successfully', 201);
            
            // Ou sans formatage (retour direct)
            // return $user;
            
        } catch (Exception $e) {
            return $this->errorResponse('User creation failed', 500);
        }
    }
}
```

### Task de Transformation
```php
class TransformDataTask implements TaskInterface
{
    public function __invoke(mixed $input, callable $next): mixed
    {
        if ($input instanceof Collection) {
            $transformed = $input->map(fn($item) => $this->transformItem($item));
        } elseif (is_array($input)) {
            $transformed = array_map([$this, 'transformItem'], $input);
        } else {
            $transformed = $this->transformItem($input);
        }

        return $next($transformed);
    }
    
    private function transformItem($item): array
    {
        return [
            'id' => $item['id'] ?? null,
            'name' => strtoupper($item['name'] ?? ''),
            'processed_at' => now(),
        ];
    }
}
```

---

## 🚀 Cas d'Usage Avancés

### 1. Process avec Branchement Conditionnel
```php
class ConditionalProcessTask implements TaskInterface
{
    public function __invoke(mixed $input, callable $next): mixed
    {
        if ($this->shouldSkipNextTasks($input)) {
            // Retourne directement sans passer au suivant
            return $this->handleEarlyReturn($input);
        }

        return $next($input);
    }
}
```

### 2. Process avec Modification du Pipeline
```php
class DynamicTaskLoader implements TaskInterface
{
    public function __invoke(mixed $input, callable $next): mixed
    {
        // Modifie l'input avant de continuer
        if (is_array($input)) {
            $input['processing_started_at'] = now();
            $input['processor'] = get_class($this);
        }

        return $next($input);
    }
}
```

### 3. Process avec Accumulation de Résultats
```php
class AccumulateResultsTask implements TaskInterface
{
    public function __invoke(mixed $input, callable $next): mixed
    {
        $result = $next($input);
        
        // Combine les résultats
        return [
            'original_input' => $input,
            'processed_result' => $result,
            'metadata' => [
                'processed_at' => now(),
                'processor' => static::class
            ]
        ];
    }
}
```

---

## ⚠️ Gestion d'Erreurs

### 1. Exception dans une Task
```php
class RiskyTask implements TaskInterface
{
    public function __invoke(mixed $input, callable $next): mixed
    {
        try {
            $result = $this->performRiskyOperation($input);
            return $next($result);
        } catch (BusinessException $e) {
            // Gérer l'erreur métier
            throw new ProcessException("Business rule violated: " . $e->getMessage());
        } catch (Exception $e) {
            // Log et re-throw
            Log::error('Task failed', ['task' => static::class, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
```

### 2. Rollback Automatique
```php
// Le Process gère automatiquement les transactions
$process = new CriticalProcess();

try {
    $result = $process->execute($data);
    // Transaction committée automatiquement
} catch (Exception $e) {
    // Transaction rollbackée automatiquement
    Log::error('Process failed', ['error' => $e->getMessage()]);
    throw $e;
}
```

### 3. Validation avec Retour d'Erreur
```php
class ValidateAndFormatTask implements TaskInterface
{
    use FormatsApiResponse;

    public function __invoke(mixed $input, callable $next): mixed
    {
        $data = $this->extractData($input);
        
        if (!$this->isValid($data)) {
            // Retourne une erreur formatée
            return $this->errorResponse('Invalid data provided', 422, [
                'errors' => $this->getValidationErrors($data)
            ]);
        }

        return $next($data);
    }
}
```

---

## 💡 Patterns Recommandés

### 1. Pattern Factory pour les Processes
```php
class ProcessFactory
{
    public static function createUserProcess(string $type): AbstractProcess
    {
        return match($type) {
            'registration' => new UserRegistrationProcess(),
            'update' => new UserUpdateProcess(),
            'deletion' => new UserDeletionProcess(),
            default => throw new InvalidArgumentException("Unknown process type: $type")
        };
    }
}

// Usage
$process = ProcessFactory::createUserProcess('registration');
$result = $process->execute($userData);
```

### 2. Pattern Decorator pour les Tasks
```php
class LoggingTaskDecorator implements TaskInterface
{
    public function __construct(
        private TaskInterface $task,
        private LoggerInterface $logger
    ) {}

    public function __invoke(mixed $input, callable $next): mixed
    {
        $this->logger->info('Task started', ['task' => get_class($this->task)]);
        
        $start = microtime(true);
        $result = $this->task->__invoke($input, $next);
        $duration = microtime(true) - $start;
        
        $this->logger->info('Task completed', [
            'task' => get_class($this->task),
            'duration' => $duration
        ]);
        
        return $result;
    }
}
```

### 3. Pattern Observer pour les Events
```php
class EventEmittingTask implements TaskInterface
{
    public function __invoke(mixed $input, callable $next): mixed
    {
        event(new TaskStarted(static::class, $input));
        
        $result = $next($input);
        
        event(new TaskCompleted(static::class, $input, $result));
        
        return $result;
    }
}
```

### 4. Pattern Strategy pour les Formats de Sortie
```php
class OutputFormatterTask implements TaskInterface
{
    public function __construct(
        private string $format = 'array'
    ) {}

    public function __invoke(mixed $input, callable $next): mixed
    {
        $result = $next($input);
        
        return match($this->format) {
            'json' => json_encode($result),
            'xml' => $this->toXml($result),
            'csv' => $this->toCsv($result),
            'array' => (array) $result,
            default => $result
        };
    }
}
```

---

## 🎯 Exemples d'Utilisation Complète

### Dans un Contrôleur API
```php
class UserController extends Controller
{
    public function store(CreateUserRequest $request)
    {
        $process = new CreateUserProcess();
        
        try {
            $result = $process->execute($request);
            
            // Le résultat peut être de n'importe quel type
            if (is_bool($result)) {
                return response()->json(['success' => $result]);
            }
            
            if ($result instanceof JsonResource) {
                return $result;
            }
            
            return response()->json($result);
            
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['error' => 'Process failed'], 500);
        }
    }
}
```

### Dans un Job Queued
```php
class ProcessUserDataJob implements ShouldQueue
{
    public function __construct(
        private array $userData
    ) {}

    public function handle()
    {
        $process = new UserDataProcessingProcess();
        
        $result = $process->execute($this->userData);
        
        // Notifier le résultat
        if ($result instanceof User) {
            event(new UserProcessed($result));
        }
    }
}
```

### Dans une Command Artisan
```php
class ProcessUsersCommand extends Command
{
    protected $signature = 'users:process {--type=all}';

    public function handle()
    {
        $users = User::all();
        $process = new BulkUserProcessingProcess();
        
        $results = $process->execute($users);
        
        $this->info("Processed {$results->count()} users successfully");
    }
}
```

---

Ce guide couvre tous les cas d'usage possibles du package avec sa nouvelle approche flexible. Le principe clé est la **liberté totale** : vous choisissez vos types d'entrée et de sortie selon vos besoins spécifiques !
