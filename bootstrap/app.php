<?php

use App\Helpers\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error(
                    code: 'VALIDATION_ERROR',
                    message: $e->getMessage() ?: 'Validation failed',
                    details: [
                        'errors' => $e->errors(),
                    ],
                    status: 422
                );
            }
        });

        // 401 unauthenticated
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error(
                    code: 'UNAUTHENTICATED',
                    message: 'Unauthenticated',
                    details: (object)[],
                    status: 401
                );
            }
        });

        // 404 model not found
        $exceptions->render(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error(
                    code: 'NOT_FOUND',
                    message: 'Resource not found',
                    details: (object)[],
                    status: 404
                );
            }
        });

        // 404 route not found
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error(
                    code: 'ROUTE_NOT_FOUND',
                    message: 'Route not found',
                    details: (object)[],
                    status: 404
                );
            }
        });

        // 405 method not allowed
        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error(
                    code: 'METHOD_NOT_ALLOWED',
                    message: 'Method not allowed',
                    details: (object)[],
                    status: 405
                );
            }
        });

        // Any other HTTP exceptions (403, 429, etc.)
        $exceptions->render(function (HttpExceptionInterface $e, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error(
                    code: 'HTTP_ERROR',
                    message: $e->getMessage() ?: 'Request failed',
                    details: [
                        'status' => $e->getStatusCode(),
                    ],
                    status: $e->getStatusCode()
                );
            }
        });

        // Fallback: any unhandled exception
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->expectsJson()) {
                return ApiResponse::error(
                    code: 'SERVER_ERROR',
                    message: app()->hasDebugModeEnabled() ? $e->getMessage() : 'Server error',
                    details: app()->hasDebugModeEnabled()
                        ? ['exception' => class_basename($e), 'trace' => collect($e->getTrace())->take(5)]
                        : (object)[],
                    status: 500
                );
            }
        });
    })
    ->create();
