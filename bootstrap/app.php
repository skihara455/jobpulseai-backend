<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // register global middleware here if needed
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 422 Validation
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'message' => 'Validation failed.',
                        'code'    => 422,
                        'errors'  => $e->errors(),
                    ],
                ], 422);
            }
        });

        // 401 Unauthenticated
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'message' => 'Unauthenticated.',
                        'code'    => 401,
                    ],
                ], 401);
            }
        });

        // 403 Forbidden
        $exceptions->render(function (AuthorizationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'message' => 'Forbidden.',
                        'code'    => 403,
                    ],
                ], 403);
            }
        });

        // 404 Not Found (models & routes)
        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'message' => 'Endpoint or resource not found.',
                        'code'    => 404,
                    ],
                ], 404);
            }
        });

        // 405 Method Not Allowed
        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'message' => 'Method not allowed.',
                        'code'    => 405,
                    ],
                ], 405);
            }
        });

        // 429 Too Many Requests
        $exceptions->render(function (ThrottleRequestsException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'message' => 'Too many requests. Please slow down.',
                        'code'    => 429,
                    ],
                ], 429);
            }
        });

        // 500 Fallback
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'message' => 'Server error.',
                        'code'    => 500,
                    ],
                ], 500);
            }
        });
    })
    ->create();
