<?php

namespace DarwinNatha\Process\Tests\Feature;

use DarwinNatha\Process\Tests\TestCase;
use Illuminate\Http\Request;
use Mockery;

class ProcessExecutionTest extends TestCase
{
    public function test_successful_process_runs_all_tasks()
    {
        $task1 = new class {
            public function __invoke(mixed $input, callable $next) {
                // Task 1 ajoute une donnée
                if (is_array($input)) {
                    $input['processed_by_task1'] = true;
                }
                return $next($input);
            }
        };

        $task2 = new class {
            public function __invoke(mixed $input, callable $next) {
                // Task 2 ajoute une donnée et retourne le résultat final
                if (is_array($input)) {
                    $input['processed_by_task2'] = true;
                }
                return ['success' => true, 'data' => $input];
            }
        };

        $payload = ['name' => 'test'];

        $process = new class($task1, $task2) extends \DarwinNatha\Process\AbstractProcess {
            public function __construct(public $t1, public $t2) {}

            public array $tasks;

            public function handle(mixed $input): mixed
            {
                $this->tasks = [$this->t1, $this->t2];
                return parent::handle($input);
            }
        };

        $result = $process->handle($payload);
        
        // Le process retourne maintenant ce que la dernière task retourne
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertTrue($result['data']['processed_by_task1']);
        $this->assertTrue($result['data']['processed_by_task2']);
    }

    public function test_backward_compatibility_with_request()
    {
        $task1 = new class {
            public function __invoke(mixed $input, callable $next) {
                // Si c'est un Request, extraire les données
                if ($input instanceof Request) {
                    $data = $input->all();
                    return ['processed' => true, 'original_data' => $data];
                }
                
                // Si c'est un array ou autre type
                return ['processed' => true, 'original_data' => $input];
            }
        };

        $request = new Request(['name' => 'test']);

        $process = new class($task1) extends \DarwinNatha\Process\AbstractProcess {
            public function __construct(public $t1) {}

            public array $tasks;

            public function handle(mixed $input): mixed
            {
                $this->tasks = [$this->t1];
                return parent::handle($input);
            }
        };

        $result = $process->handle($request);
        $this->assertIsArray($result);
        $this->assertTrue($result['processed']);
        $this->assertEquals('test', $result['original_data']['name']);
    }
}
