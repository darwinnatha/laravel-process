<?php

namespace DarwinNatha\Process\Contracts;

use DarwinNatha\Process\Support\ProcessPayload;
use Illuminate\Http\Request;

interface TaskInterface
{
    /**
     * Exécute la tâche sur le payload et la passe au prochain middleware.
     *
     * @param  ProcessPayload  $payload
     * @param  callable  $next
     * @return mixed
     */
    public function __invoke(ProcessPayload $payload, callable $next): mixed;
}