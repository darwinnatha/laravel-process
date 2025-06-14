<?php

namespace DarwinNatha\Process\Tests\Feature;

use DarwinNatha\Process\Tests\TestCase;
use Illuminate\Http\Request;
use Mockery;

class ProcessExecutionTest extends TestCase
{
    public function test_successful_process_runs_all_tasks()
    {
        $task1 = Mockery::mock();
        $task1->shouldReceive('__invoke')->andReturnUsing(fn(Request $request, callable $next) => $next($request));

        $task2 = Mockery::mock();
        $task2->shouldReceive('__invoke')->andReturnUsing(fn(Request $request, callable $next) => $next($request));

        $request = new Request(['name' => 'test']);

        $process = new class($task1, $task2) extends \DarwinNatha\Process\AbstractProcess {
            public function __construct(public $t1, public $t2) {}

            public array $tasks;

            public function handle(Request $request): array
            {
                $this->tasks = [$this->t1, $this->t2];
                return parent::handle($request);
            }
        };

        $result = $process->handle($request);
        // The handle return and array with code, status success, message and data
        $this->assertIsArray($result);
    }
}
