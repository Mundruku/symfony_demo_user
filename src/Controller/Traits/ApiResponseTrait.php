<?php
namespace App\Controller\Traits;

use Symfony\Component\HttpFoundation\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Generic API Success Response
     */
    protected function successResponse(
        mixed $data, 
        int $status = 200
    ): JsonResponse {
        return $this->json(
            ['data' => $data],
            $status
        );
    }

    /**
     * Generic API Error Response
     */
    protected function errorResponse(
        string $message, 
        int $status = 400, 
        array $errors = []
    ): JsonResponse {
        return $this->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ], $status);
    }
}