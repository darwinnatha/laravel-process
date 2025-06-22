<?php

namespace DarwinNatha\Process\Contracts;

interface TaskInterface
{
    /**
     * Exécute la tâche sur n'importe quel type de données
     *
     * @param  mixed  $input - Array, Object, Collection, Request, DTO, etc.
     * @param  callable  $next
     * @return mixed
     */
    public function __invoke(mixed $input, callable $next): mixed;
}