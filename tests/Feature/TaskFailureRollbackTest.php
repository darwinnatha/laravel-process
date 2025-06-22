<?php

namespace DarwinNatha\Process\Tests\Feature;

use DarwinNatha\Process\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery;

class TaskFailureRollbackTest extends TestCase
{
    protected function setupDatabaseMocks(): void
    {
        // Ne pas faire de mocks par défaut, ce test définit les siens
    }

    public function test_failed_task_rolls_back_transaction()
    {
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();
        DB::shouldReceive('commit')->never();

        $failingTask = new class {
            public function __invoke(mixed $input, callable $next) {
                throw new \RuntimeException('Task failed');
            }
        };

        $process = new class($failingTask) extends \DarwinNatha\Process\AbstractProcess {
            public function __construct(public $fail) {}
            public array $tasks;

            public function handle(mixed $input): mixed
            {
                $this->tasks = [$this->fail];
                return parent::handle($input);
            }
        };

        // Maintenant le process lance l'exception au lieu de retourner un tableau formaté
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Task failed');
        
        $process->handle(new Request(['name' => 'fail']));
    }
}
