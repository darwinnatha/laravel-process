# Laravel Process

A **completely flexible** and **customizable** process orchestration package for Laravel.  
Structure your business logic as **processes** composed of **tasks** with **total control** over the base implementation.

---

## ✨ Key Philosophy

**Ultimate Flexibility** - **Publish and customize everything**:
- 📥 **Universal Input** - Accept any type: Arrays, Objects, Collections, Requests, DTOs, etc.
- 📤 **Universal Output** - Return any type: Boolean, Array, DTO, Resource, Collection, null, etc.
- 🎛️ **Customizable Base Class** - Publish AbstractProcess and modify it as needed
- 🚀 **Zero Constraints** - No forced patterns, complete developer freedom

---

## ✨ Features

- **Publishable Base Class** - Customize AbstractProcess to your needs
- **Universal Input/Output** - `mixed` types for maximum flexibility  
- **Pipeline-Based** - Leverages Laravel's `Pipeline` for task chaining
- **Automatic Transactions** - Built-in DB transaction handling with rollback
- **Optional API Formatting** - Use `FormatsApiResponse` trait when needed
- **Artisan Commands** - Generate processes and tasks easily

---

## 📦 Installation

```bash
composer require darwinnatha/laravel-process
```

---

## 🚀 Quick Start

### 1. Publish the AbstractProcess (Recommended)

```bash
php artisan process:publish
```

This creates `app/Processes/AbstractProcess.php` that you can customize:
- Add logging, monitoring, caching
- Modify transaction handling  
- Add middleware or custom validation
- Implement your error handling strategy

### 2. Create a Process

```bash
php artisan make:process CreateUserProcess --group=User
```

### 3. Create Tasks

```bash
php artisan make:task ValidateUserData --group=User
php artisan make:task SaveUserToDatabase --group=User
```

---

## 🧠 How It Works

After publishing, your **AbstractProcess** becomes completely yours to customize. All processes extend from **your** base class.

### Example: Customized AbstractProcess

```php
// app/Processes/AbstractProcess.php
abstract class AbstractProcess
{
    public array $tasks = [];

    public function handle(mixed $input): mixed
    {
        // Your custom logging
        Log::info('Process started', ['process' => static::class]);

        // Your custom validation
        $this->validateInput($input);

        // Your custom transaction handling
        DB::beginTransaction();
        try {
            $result = Pipeline::send($input)
                ->through($this->getMiddleware())  // Your middleware
                ->through($this->tasks)
                ->thenReturn();
                
            DB::commit();
            
            // Your custom success handling
            $this->onSuccess($result);
            return $result;
            
        } catch (Throwable $e) {
            DB::rollBack();
            
            // Your custom error handling
            $this->onError($e);
            throw $e;
        }
    }

    // Your custom methods
    protected function validateInput(mixed $input): void { /* ... */ }
    protected function getMiddleware(): array { return []; }
    protected function onSuccess(mixed $result): void { /* ... */ }
    protected function onError(Throwable $e): void { /* ... */ }
}
```

### Example: Your Process

```php
namespace App\Processes\User;

use App\Processes\AbstractProcess;  // Your custom base class

class CreateUserProcess extends AbstractProcess
{
    public array $tasks = [
        Tasks\ValidateUserData::class,
        Tasks\SaveUserToDatabase::class,
        Tasks\SendWelcomeEmail::class,
    ];
}
```

### Example: Flexible Task

```php
final class ValidateUserData implements TaskInterface
{
    public function __invoke(mixed $input, callable $next): mixed
    {
        // Handle any input type
        $data = match(true) {
            $input instanceof Request => $input->validated(),
            is_array($input) => $input,
            is_object($input) => (array) $input,
            default => throw new InvalidArgumentException('Unsupported input')
        };

        // Your validation logic
        if (!$this->isValid($data)) {
            throw new ValidationException('Invalid data');
        }

        return $next($input);
    }
}
```

---

## 🚀 Usage Examples

### With Different Input Types

```php
$process = new CreateUserProcess();

// With an array
$result = $process->handle(['name' => 'John', 'email' => 'john@test.com']);

// With a Request
$result = $process->handle($request);

// With a DTO  
$result = $process->handle($userDto);

// With any custom object
$result = $process->handle($customObject);
```

---

## 🎛️ Optional API Response Formatting

If you want standardized API responses, use the **optional** `FormatsApiResponse` trait:

```php
use DarwinNatha\Process\Traits\FormatsApiResponse;

final class CreateUserTask implements TaskInterface
{
    use FormatsApiResponse; // Optional!

    public function __invoke(mixed $input, callable $next): mixed
    {
        try {
            $user = User::create($this->extractData($input));
            return $this->created($user, 'User created successfully');
        } catch (Exception $e) {
            return $this->error('User creation failed', ['error' => $e->getMessage()]);
        }
    }
}
```

**This is completely optional** - by default, return whatever you want!

---

## ⚙️ Commands

### Publish AbstractProcess
```bash
php artisan process:publish          # Publish for customization
php artisan process:publish --force  # Overwrite existing
```

### Generate Classes
```bash
php artisan make:process LoginProcess --group=Auth
php artisan make:task ValidateCredentials --group=Auth
```

---

## 🎯 Advanced Customization Examples

### Add Caching to Your AbstractProcess

```php
public function handle(mixed $input): mixed
{
    $cacheKey = $this->getCacheKey($input);
    
    if ($cacheKey && Cache::has($cacheKey)) {
        return Cache::get($cacheKey);
    }
    
    $result = $this->executeProcess($input);
    
    if ($cacheKey) {
        Cache::put($cacheKey, $result, $this->getCacheTtl());
    }
    
    return $result;
}
```
---

## 🧪 Testing

Run tests:

```bash
vendor/bin/pest
```

The package includes comprehensive tests covering:
* AbstractProcess publication and customization
* Flexible input/output handling
* Different data types (arrays, objects, DTOs, Requests)
* Transaction rollback on failures
* Console command generation

---

## 🧑‍💻 Author

**Darwin Piegue (DarwinNatha)**
🔗 [github.com/darwinnatha](https://github.com/darwinnatha)

---

## ⚖️ License

MIT License — free to use, modify, and distribute.
