<?php

namespace DarwinNatha\Process\Traits;

use Throwable;

/**
 * Trait optionnel pour formatter les réponses en style API
 * 
 * Utilisez ce trait dans vos Tasks si vous voulez un format API standardisé,
 * sinon laissez vos Process retourner ce que vous voulez !
 */
trait FormatsApiResponse
{
    /**
     * Formate une réponse de succès
     */
    protected function success(mixed $data = null, string $message = 'Operation completed successfully', int $code = 200): array
    {
        return [
            'code' => $code,
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * Formate une réponse d'erreur
     */
    protected function error(string $message = 'An error occurred', mixed $errors = null, int $code = 400): array
    {
        return [
            'code' => $code,
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ];
    }

    /**
     * Formate une réponse d'erreur serveur
     */
    protected function serverError(string $message = 'Internal server error', mixed $errors = null): array
    {
        return $this->error($message, $errors, 500);
    }

    /**
     * Formate une réponse de validation échouée
     */
    protected function validationError(string $message = 'Validation failed', mixed $errors = null): array
    {
        return $this->error($message, $errors, 422);
    }

    /**
     * Formate une réponse non autorisée
     */
    protected function unauthorized(string $message = 'Unauthorized'): array
    {
        return $this->error($message, null, 401);
    }

    /**
     * Formate une réponse interdite
     */
    protected function forbidden(string $message = 'Forbidden'): array
    {
        return $this->error($message, null, 403);
    }

    /**
     * Formate une réponse non trouvé
     */
    protected function notFound(string $message = 'Resource not found'): array
    {
        return $this->error($message, null, 404);
    }

    /**
     * Formate une exception en réponse
     */
    protected function exceptionResponse(Throwable $e, bool $debug = false): array
    {
        $errors = null;
        
        if ($debug) {
            $errors = [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(5)->toArray(),
            ];
        }

        return $this->serverError('Internal server error', $errors);
    }

    /**
     * Raccourci pour créer une réponse paginée
     */
    protected function paginated($data, string $message = 'Data retrieved successfully'): array
    {
        if (method_exists($data, 'toArray')) {
            $data = $data->toArray();
        }

        return $this->success($data, $message);
    }

    /**
     * Raccourci pour créer une réponse de création
     */
    protected function created(mixed $data = null, string $message = 'Resource created successfully'): array
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Raccourci pour créer une réponse de mise à jour
     */
    protected function updated(mixed $data = null, string $message = 'Resource updated successfully'): array
    {
        return $this->success($data, $message);
    }

    /**
     * Raccourci pour créer une réponse de suppression
     */
    protected function deleted(string $message = 'Resource deleted successfully'): array
    {
        return $this->success(null, $message);
    }

    /**
     * Raccourci pour créer une réponse "no content"
     */
    protected function noContent(): array
    {
        return $this->success(null, 'No content', 204);
    }
}
