<?php

namespace DarwinNatha\Process\Exceptions;

use Exception;

class TaskException extends Exception
{
    protected array $context = [];

    public static function withMessage(string $message, array $context = []): static
    {
        $e = new static($message);
        $e->context = $context;

        return $e;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
