<?php

declare(strict_types=1);

namespace DarwinNatha\Process;

use DarwinNatha\Process\Support\ResponseFormatter;
use Illuminate\Http\Request;
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
     * @throws Throwable
     */
    public function handle(Request $request): array
    {
        DB::beginTransaction();
        try {
            $result = Pipeline::send($request)
                ->through($this->tasks)
                ->thenReturn();
            DB::commit();
            $message = $result['message'] ?? 'Process completed successfully';
            unset($result['message']);
            if (isset($result['code']) && $result['code'] >= 400) {
                return ResponseFormatter::formatException(new \Exception($message), true);
            }
            
            return ResponseFormatter::formatSuccess($result, $message);
        } catch (Throwable $e) {
            DB::rollBack();
            return ResponseFormatter::formatException($e);
        }
    }
}
