<?php

declare(strict_types=1);

use App\Http\Middleware\AcceptJson;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
        api: __DIR__ . '/../routes/api/routes.php',
        commands: __DIR__ . '/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prependToGroup('api', AcceptJson::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TooManyRequestsHttpException $exception, Request $request): ?JsonResponse {
            if ( ! $request->expectsJson()) {
                return null;
            }

            $response = new JsonResponse([
                'message' => 'Too many requests.',
            ], Response::HTTP_TOO_MANY_REQUESTS);

            $retryAfter = $exception->getHeaders()['Retry-After'] ?? null;
            if (null !== $retryAfter) {
                $response->headers->set('Retry-After', (string) $retryAfter);
            }

            return $response;
        });

        $exceptions->render(function (ValidationException $exception, Request $request): ?JsonResponse {
            if ( ! $request->expectsJson()) {
                return null;
            }

            return new JsonResponse([
                'message' => 'Validation failed.',
                'errors' => $exception->errors(),
            ], $exception->status);
        });

        $exceptions->render(function (InvalidSignatureException $exception, Request $request): ?JsonResponse {
            if ( ! $request->expectsJson()) {
                return null;
            }

            return new JsonResponse([
                'message' => 'Invalid signature.',
            ], Response::HTTP_FORBIDDEN);
        });
    })->create();
