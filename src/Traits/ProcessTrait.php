<?php

namespace DarwinNatha\Process\Traits;

use Illuminate\Http\Request;

trait ProcessTrait
{
     public function runProcess(string $processClass, Request $request): array
    {
        /** @var \DarwinNatha\Process\AbstractProcess $process */
        $process = app($processClass);
        $result = $process->handle($request);

        return [$result['code'], $result];
    }
}
