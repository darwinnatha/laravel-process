<?php

namespace DarwinNatha\Process\Traits;

use DarwinNatha\Process\Support\ProcessPayload;
use Illuminate\Http\Request;

trait ProcessTrait
{
     public function runProcess(string $processClass, Request|ProcessPayload $request): array
    {
        /** @var \DarwinNatha\Process\AbstractProcess $process */
        $process = app($processClass);
        if ($request instanceof Request) {
            // Convert Request to ProcessPayload
            $request = ProcessPayload::fromRequest($request);
        } 
        $result = $process->execute($request);

        return [$result['code'], $result];
    }
}
