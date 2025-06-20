<?php

namespace DarwinNatha\Process\Tests\Feature;

use DarwinNatha\Process\Support\ProcessPayload;
use DarwinNatha\Process\Tests\TestCase;
use Illuminate\Http\Request;
use Mockery;

class ProcessExecutionTest extends TestCase
{
    public function test_successful_process_runs_all_tasks()
    {
        $task1 = Mockery::mock();
        $task1->shouldReceive('__invoke')->andReturnUsing(function(\DarwinNatha\Process\Support\ProcessPayload $payload, callable $next) {
            return $next($payload);
        });

        $task2 = Mockery::mock();
        $task2->shouldReceive('__invoke')->andReturnUsing(function(\DarwinNatha\Process\Support\ProcessPayload $payload, callable $next) {
            return $next($payload);
        });

        $payload = ProcessPayload::make(['name' => 'test']);

        $process = new class($task1, $task2) extends \DarwinNatha\Process\AbstractProcess {
            public function __construct(public $t1, public $t2) {}

            public array $tasks;

            public function execute(\DarwinNatha\Process\Support\ProcessPayload $payload): array
            {
                $this->tasks = [$this->t1, $this->t2];
                return parent::execute($payload);
            }
        };

        $result = $process->execute($payload);
        // The execute return and array with code, status success, message and data
        $this->assertIsArray($result);
    }

    public function test_backward_compatibility_with_request()
    {
        $task1 = Mockery::mock();
        $task1->shouldReceive('__invoke')->andReturnUsing(function(\DarwinNatha\Process\Support\ProcessPayload $payload, callable $next) {
            return $next($payload);
        });

        $request = new Request(['name' => 'test']);

        $process = new class($task1) extends \DarwinNatha\Process\AbstractProcess {
            public function __construct(public $t1) {}

            public array $tasks;

            public function handle(Request $request): array
            {
                $this->tasks = [$this->t1];
                return parent::handle($request);
            }
        };

        $result = $process->handle($request);
        $this->assertIsArray($result);
    }
}
