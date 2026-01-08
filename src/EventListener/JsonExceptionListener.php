<?php
// src/EventListener/JsonExceptionListener.php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class JsonExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();
        
        // Only handle JSON requests or API routes
        if ($request->getContentTypeFormat() !== 'json' && 
            !str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }
        
        $statusCode = $exception instanceof HttpExceptionInterface 
            ? $exception->getStatusCode() 
            : 500;
        
        $responseData = [
            'error' => [
                'code' => $statusCode,
                'message' => $exception->getMessage(),
            ],
        ];
        
        // Add debug info in dev environment
        if ($_ENV['APP_ENV'] === 'dev') {
            $responseData['error']['trace'] = $exception->getTrace();
            $responseData['error']['file'] = $exception->getFile();
            $responseData['error']['line'] = $exception->getLine();
        }
        
        $response = new JsonResponse($responseData, $statusCode);
        $response->headers->set('Content-Type', 'application/json');
        
        $event->setResponse($response);
    }
}