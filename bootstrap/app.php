<?php

declare(strict_types=1);

use App\Domain\Wallet\Exceptions\CannotRevertTransactionException;
use App\Domain\Wallet\Exceptions\CannotTransferToSelfException;
use App\Domain\Wallet\Exceptions\InsufficientBalanceException;
use App\Domain\Wallet\Exceptions\InvalidTransactionAmountException;
use App\Domain\Wallet\Exceptions\TransactionAlreadyReversedException;
use App\Domain\Wallet\Exceptions\TransactionDomainException;
use App\Domain\Wallet\Exceptions\TransactionNotFoundException;
use App\Domain\Wallet\Exceptions\WalletDomainException;
use App\Domain\Wallet\Exceptions\WalletNotFoundException;
use App\Http\Middleware\AcceptJson;
use App\Support\AuditLog;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
        api: __DIR__ . '/../routes/api/routes.php',
        commands: __DIR__ . '/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'abilities' => CheckAbilities::class,
        ]);
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

            AuditLog::log('http.rate_limit', []);

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

            AuditLog::log('http.validation_failed', [
                'exception' => $exception::class,
            ]);

            return new JsonResponse([
                'message' => 'Validation failed.',
                'errors' => $exception->errors(),
            ], $exception->status);
        });

        $exceptions->render(function (InvalidSignatureException $exception, Request $request): ?JsonResponse {
            if ( ! $request->expectsJson()) {
                return null;
            }

            AuditLog::log('http.invalid_signature', [
                'exception' => $exception::class,
            ]);

            return new JsonResponse([
                'message' => 'Invalid signature.',
            ], Response::HTTP_FORBIDDEN);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request): ?JsonResponse {
            if ( ! $request->expectsJson()) {
                return null;
            }

            AuditLog::log('auth.forbidden', [
                'exception' => $exception::class,
            ]);

            return new JsonResponse([
                'message' => 'Forbidden.',
            ], Response::HTTP_FORBIDDEN);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request): ?JsonResponse {
            if ( ! $request->expectsJson()) {
                return null;
            }

            AuditLog::log('auth.unauthorized', [
                'guard' => 'sanctum',
            ]);

            return new JsonResponse([
                'message' => 'Unauthorized.',
            ], Response::HTTP_UNAUTHORIZED);
        });

        $exceptions->render(function (HttpException $exception, Request $request): ?JsonResponse {
            if ( ! $request->expectsJson()) {
                return null;
            }

            AuditLog::log('http.exception', [
                'exception' => $exception::class,
            ]);

            return new JsonResponse([
                'message' => $exception->getMessage(),
            ], $exception->getStatusCode());
        });

        $exceptions->render(function (WalletDomainException $exception) {
            $statusCode = match (true) {
                $exception instanceof WalletNotFoundException => Response::HTTP_NOT_FOUND,
                $exception instanceof InvalidTransactionAmountException => Response::HTTP_UNPROCESSABLE_ENTITY,
                $exception instanceof CannotTransferToSelfException => Response::HTTP_UNPROCESSABLE_ENTITY,
                $exception instanceof InsufficientBalanceException => Response::HTTP_UNPROCESSABLE_ENTITY,
                default => Response::HTTP_BAD_REQUEST,
            };

            AuditLog::log('wallet.transaction.failed', [
                'exception' => $exception::class,
                'identifier' => $exception->identifier,
            ]);

            return response()->json([
                'error' => class_basename($exception),
                'message' => $exception->getMessage(),
            ], $statusCode);
        });

        $exceptions->render(function (TransactionDomainException $exception) {
            $statusCode = match (true) {
                $exception instanceof TransactionNotFoundException => Response::HTTP_NOT_FOUND,
                $exception instanceof CannotRevertTransactionException => Response::HTTP_UNPROCESSABLE_ENTITY,
                $exception instanceof TransactionAlreadyReversedException => Response::HTTP_UNPROCESSABLE_ENTITY,
                default => Response::HTTP_BAD_REQUEST,
            };

            AuditLog::log('wallet.transaction.failed', [
                'exception' => $exception::class,
                'identifier' => $exception->identifier,
            ]);

            return response()->json([
                'error' => class_basename($exception),
                'message' => $exception->getMessage(),
            ], $statusCode);
        });
    })->create();
