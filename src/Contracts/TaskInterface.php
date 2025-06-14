<?php

namespace DarwinNatha\Process\Contracts;

use Illuminate\Http\Request;

interface TaskInterface
{
    /**
     * Exécute la tâche sur la requête et la passe au prochain middleware.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     * @return mixed
     */
    public function __invoke(Request $request, callable $next): mixed;
}