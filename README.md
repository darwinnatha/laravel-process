# Laravel Process

A lightweight and elegant pipeline-based process orchestration package for Laravel.  
It lets you structure your business logic as **processes** composed of **tasks**, with built-in error handling, automatic transactions, and a consistent return format.

---

## ✨ Features

- Organize logic into **processes** and **tasks**
- Leverages Laravel's `Pipeline` for task chaining
- Automatically handles **transactions** and **error catching**
- Unified response structure: `code`, `status`, `message`, `data`
- Artisan commands to generate **process** and **task** classes
- Stub-based generation for easy customization
- `ProcessorTrait` for seamless controller integration

---

## 📦 Installation

> Note: The package name (`darwinnatha/laravel-process`) matches the repository name for clarity.

```bash
composer require darwinnatha/laravel-process
````markdown
```
```
````

---

## 🧠 How It Works

A **Process** is a class that chains a set of **Tasks** using Laravel’s pipeline.

### Example: LoginProcess

```php
namespace App\Processes\Auth;

class LoginProcess extends AbstractProcess
{
    public array $tasks = [
        Tasks\DetermineUser::class,
        Tasks\GenerateToken::class,
        Tasks\FinalizeLogin::class,
    ];
}
```

Each **Task** is a single-purpose class that implements `__invoke(Request $request, Closure $next)`.

```php
final class DetermineUser
{
    public function __invoke(Request $request, Closure $next): mixed
    {
        $user = User::where('phone_number', $request->phone_number)->first();

        if (! $user) {
            return [
                'code' => 404,
                'status' => 'error',
                'message' => 'User not found',
            ];
        }

        $request->merge(['user_id' => $user->id]);

        return $next($request);
    }
}
```

---

## 🧰 Using a Process in Controllers

Use the `ProcessorTrait` to cleanly run a process and handle the response:

```php
use DarwinNatha\Process\Traits\ProcessorTrait;

class AuthController extends Controller
{
    use ProcessorTrait;

    public function login(LoginRequest $request)
    {
        [$code, $result] = $this->runProcess(LoginProcess::class, $request);
        return response()->json($result, $code);
    }
}
```

---

## ⚙️ Generate Classes

Create a process or a task with:

```bash
php artisan make:process LoginProcess --group=Auth
php artisan make:task DetermineUser --group=Auth
```

> If `--group` or `--name` is not passed, the command will interactively ask for the values.

This creates:

* `app/Processes/Auth/LoginProcess.php`
* `app/Processes/Auth/Tasks/DetermineUser.php`

Missing folders are automatically created and **capitalized properly**.

Use `--force` to overwrite existing files, or you'll be prompted.

---

## 📁 Folder Structure

```
src/
├── Commands/
│   ├── MakeProcessCommand.php
│   └── MakeTaskCommand.php
├── Contracts/
│   ├── ProcessInterface.php
│   └── TaskInterface.php
├── Exceptions/
│   └── ProcessException.php
├── Traits/
│   └── ProcessorTrait.php
├── stubs/
│   ├── process.stub
│   └── task.stub
└── ProcessServiceProvider.php
```

---

## 🔧 Customizing Stubs

Want to modify the stub templates? Just publish them:

```bash
php artisan vendor:publish --tag=process-stubs
```

---

## 🧪 Testing

The package includes full feature testing using:

* ✅ [PestPHP](https://pestphp.com)
* 🔁 [Mockery](https://github.com/mockery/mockery)
* 🧪 [Orchestra Testbench](https://github.com/orchestral/testbench)

Run tests with:

```bash
vendor/bin/pest
```

Tests cover:

* Process execution and flow
* Task chaining and modification
* Console command behavior and file generation

---

## 🧑‍💻 Author

**Darwin Piegue (DarwinNatha)**
🔗 [github.com/darwinnatha](https://github.com/darwinnatha)

---

## ⚖️ License

MIT License — free to use, modify, and distribute.

```

---