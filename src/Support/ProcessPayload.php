<?php

declare(strict_types=1);

namespace DarwinNatha\Process\Support;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use JsonSerializable;

/**
 * ProcessPayload - Conteneur de données générique pour les Process
 * 
 * Remplace la dépendance directe à Illuminate\Http\Request
 * Peut être créé depuis HTTP, Jobs, CLI, Events, etc.
 */
class ProcessPayload implements ArrayAccess, Arrayable, Jsonable, JsonSerializable
{
    protected array $data = [];
    protected array $metadata = [];

    public function __construct(array $data = [], array $metadata = [])
    {
        $this->data = $data;
        $this->metadata = $metadata;
    }

    /**
     * Créer un payload depuis une Request HTTP
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            data: $request->all(),
            metadata: [
                'source' => 'http',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'user_id' => $request->user()?->id,
            ]
        );
    }

    /**
     * Créer un payload depuis un Job
     */
    public static function fromJob(array $data, ?string $jobClass = null): self
    {
        return new self(
            data: $data,
            metadata: [
                'source' => 'job',
                'job_class' => $jobClass,
                'timestamp' => now()->toISOString(),
            ]
        );
    }

    /**
     * Créer un payload depuis une commande CLI
     */
    public static function fromCommand(array $data, ?string $commandName = null): self
    {
        return new self(
            data: $data,
            metadata: [
                'source' => 'cli',
                'command' => $commandName,
                'timestamp' => now()->toISOString(),
            ]
        );
    }

    /**
     * Créer un payload depuis un Event
     */
    public static function fromEvent(array $data, ?string $eventClass = null): self
    {
        return new self(
            data: $data,
            metadata: [
                'source' => 'event',
                'event_class' => $eventClass,
                'timestamp' => now()->toISOString(),
            ]
        );
    }

    /**
     * Créer un payload générique
     */
    public static function make(array $data = [], array $metadata = []): self
    {
        return new self($data, $metadata);
    }

    // === Accès aux données ===

    public function get(string $key, $default = null)
    {
        return data_get($this->data, $key, $default);
    }

    public function set(string $key, $value): self
    {
        data_set($this->data, $key, $value);
        return $this;
    }

    public function has(string $key): bool
    {
        return data_get($this->data, $key) !== null;
    }

    public function all(): array
    {
        return $this->data;
    }

    public function only(array $keys): array
    {
        return collect($this->data)->only($keys)->toArray();
    }

    public function except(array $keys): array
    {
        return collect($this->data)->except($keys)->toArray();
    }

    // === Métadonnées ===

    public function getMetadata(string $key = null)
    {
        return $key ? data_get($this->metadata, $key) : $this->metadata;
    }

    public function setMetadata(string $key, $value): self
    {
        data_set($this->metadata, $key, $value);
        return $this;
    }

    public function getSource(): ?string
    {
        return $this->getMetadata('source');
    }

    // === Interfaces ===

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    public function jsonSerialize(): array
    {
        return [
            'data' => $this->data,
            'metadata' => $this->metadata,
        ];
    }

    // === Helpers ===

    public function merge(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function collect(): Collection
    {
        return collect($this->data);
    }

    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function __get(string $key)
    {
        return $this->get($key);
    }

    public function __set(string $key, $value): void
    {
        $this->set($key, $value);
    }

    public function __isset(string $key): bool
    {
        return $this->has($key);
    }
}
