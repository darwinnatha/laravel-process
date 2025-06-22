<?php

declare(strict_types=1);

namespace DarwinNatha\Process;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Pipeline;
use Throwable;

abstract class AbstractProcess
{
    /**
     * @var array<int,string|class-string>
     */ 
    public array $tasks = [];

    /**
     * Handle the incoming input (any type).
     * 
     * @param mixed $input - Array, Object, Collection, Request, DTO, etc.
     * @return mixed
     * @throws Throwable
     */
    public function handle(mixed $input): mixed
    {
        DB::beginTransaction();
        try {
            $result = Pipeline::send($input)
                ->through($this->tasks)
                ->thenReturn();
            DB::commit();
            
            return $result;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
