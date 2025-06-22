<?php

declare(strict_types=1);

namespace DarwinNatha\Process;

use DarwinNatha\Process\Support\ProcessPayload;
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
     * Point d'entrée principal avec ProcessPayload
     * 
     * @throws Throwable
     */
    public function execute(ProcessPayload $payload): array
    {
        DB::beginTransaction();
        try {
            $result = Pipeline::send($payload)
                ->through($this->tasks)
                ->thenReturn();
            DB::commit();
            $message = $result['message'] ?? 'Process completed successfully';
            unset($result['message']);
            if (isset($result['code']) && $result['code'] >= 400) {
                DB::rollBack();
                return ResponseFormatter::formatSuccess($result, $message);
            }

            return ResponseFormatter::formatSuccess($result, $message);
        } catch (Throwable $e) {
            DB::rollBack();
            return ResponseFormatter::formatException($e, config('app.debug') ?? false);
        }
    }

    /**
     * Méthode de compatibilité avec Request (deprecated)
     * 
     * @deprecated Utilisez execute(ProcessPayload) à la place
     * @throws Throwable
     */
    public function handle(Request $request): array
    {
        return $this->execute(ProcessPayload::fromRequest($request));
    }
}
