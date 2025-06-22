<?php

namespace DarwinNatha\Process\Tests\Feature;

use DarwinNatha\Process\Tests\TestCase;
use DarwinNatha\Process\Traits\FormatsApiResponse;

class FlexibleResponsesTest extends TestCase
{
    public function test_process_can_return_simple_boolean()
    {
        $task = new class {
            public function __invoke(mixed $input, callable $next)
            {
                return true;
            }
        };

        $process = new class($task) extends \DarwinNatha\Process\AbstractProcess {
            public function __construct(public $t) {}
            public array $tasks;

            public function handle(mixed $input): mixed
            {
                $this->tasks = [$this->t];
                return parent::handle($input);
            }
        };

        $result = $process->handle([]);
        $this->assertTrue($result);
    }

    public function test_process_can_return_model_resource()
    {
        $task = new class {
            public function __invoke(mixed $input, callable $next)
            {
                return ['user' => ['id' => 1, 'name' => 'Test User']];
            }
        };

        $process = new class($task) extends \DarwinNatha\Process\AbstractProcess {
            public function __construct(public $t) {}
            public array $tasks;

            public function handle(mixed $input): mixed
            {
                $this->tasks = [$this->t];
                return parent::handle($input);
            }
        };

        $result = $process->handle([]);
        $this->assertIsArray($result);
        $this->assertEquals('Test User', $result['user']['name']);
    }

    public function test_process_can_return_collection()
    {
        $task = new class {
            public function __invoke(mixed $input, callable $next)
            {
                return collect(['item1', 'item2', 'item3']);
            }
        };

        $process = new class($task) extends \DarwinNatha\Process\AbstractProcess {
            public function __construct(public $t) {}
            public array $tasks;

            public function handle(mixed $input): mixed
            {
                $this->tasks = [$this->t];
                return parent::handle($input);
            }
        };

        $result = $process->handle([]);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
        $this->assertCount(3, $result);
    }

    public function test_task_with_api_response_trait()
    {
        $task = new class {
            use FormatsApiResponse;

            public function __invoke(mixed $input, callable $next)
            {
                // Simule une création d'utilisateur
                $name = is_array($input) ? ($input['name'] ?? 'Unknown') : 'Unknown';
                $user = ['id' => 123, 'name' => $name];
                
                return $this->created($user, 'User created successfully');
            }
        };

        $process = new class($task) extends \DarwinNatha\Process\AbstractProcess {
            public function __construct(public $t) {}
            public array $tasks;

            public function handle(mixed $input): mixed
            {
                $this->tasks = [$this->t];
                return parent::handle($input);
            }
        };

        $result = $process->handle(['name' => 'Alice']);
        
        $this->assertIsArray($result);
        $this->assertEquals(201, $result['code']);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('User created successfully', $result['message']);
        $this->assertEquals('Alice', $result['data']['name']);
    }

    public function test_task_with_api_error_response()
    {
        $task = new class {
            use FormatsApiResponse;

            public function __invoke(mixed $input, callable $next)
            {
                return $this->error('Validation failed', [
                    'email' => ['Email is required']
                ], 422);
            }
        };

        $process = new class($task) extends \DarwinNatha\Process\AbstractProcess {
            public function __construct(public $t) {}
            public array $tasks;

            public function handle(mixed $input): mixed
            {
                $this->tasks = [$this->t];
                return parent::handle($input);
            }
        };

        $result = $process->handle([]);
        
        $this->assertIsArray($result);
        $this->assertEquals(422, $result['code']);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Validation failed', $result['message']);
        $this->assertArrayHasKey('email', $result['errors']);
    }

    public function test_multiple_tasks_with_different_return_types()
    {
        $task1 = new class {
            public function __invoke($input, $next) {
                // Task 1 modifie l'input et continue
                if (is_array($input)) {
                    $input['processed_step1'] = true;
                }
                return $next($input);
            }
        };

        $task2 = new class {
            public function __invoke($input, $next) {
                // Task 2 ajoute plus de données et continue
                if (is_array($input)) {
                    $input['processed_step2'] = true;
                }
                return $next($input);
            }
        };

        $task3 = new class {
            use FormatsApiResponse;

            public function __invoke(mixed $input, callable $next)
            {
                // Task 3 retourne le résultat final formaté
                $data = is_array($input) ? $input : ['data' => $input];
                return $this->success($data, 'Process completed');
            }
        };

        $process = new class($task1, $task2, $task3) extends \DarwinNatha\Process\AbstractProcess {
            public function __construct(public $t1, public $t2, public $t3) {}
            public array $tasks;

            public function handle(mixed $input): mixed
            {
                $this->tasks = [$this->t1, $this->t2, $this->t3];
                return parent::handle($input);
            }
        };

        $result = $process->handle(['original' => 'data']);
        
        $this->assertIsArray($result);
        $this->assertEquals(200, $result['code']);
        $this->assertEquals('success', $result['status']);
        $this->assertTrue($result['data']['processed_step1']);
        $this->assertTrue($result['data']['processed_step2']);
    }

    public function test_process_can_return_null()
    {
        $task = new class {
            public function __invoke(mixed $input, callable $next)
            {
                // Task qui ne retourne rien (void operations)
                return null;
            }
        };

        $process = new class($task) extends \DarwinNatha\Process\AbstractProcess {
            public function __construct(public $t) {}
            public array $tasks;

            public function handle(mixed $input): mixed
            {
                $this->tasks = [$this->t];
                return parent::handle($input);
            }
        };

        $result = $process->handle([]);
        $this->assertNull($result);
    }
}
