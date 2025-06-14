<?php

namespace DarwinNatha\Process\Support;

use Throwable;

class ResponseFormatter
{
     public static function formatSuccess(mixed $data, string $message = 'Process completed'): array
    {
        return [
            'code' => 200,
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ];
    }

    public static function formatException(Throwable $e, bool $debug = false): array
    {
        return [
            'code' => 500,
            'status' => 'error',
            'message' => 'Internal server error',
            'errors' => $debug ? [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(5),
            ] : null,
        ];
    }
}
