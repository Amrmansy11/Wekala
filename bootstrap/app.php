<?php

use Illuminate\Http\Request;
use App\Http\Middleware\Localization;
use Illuminate\Support\Facades\Route;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Auth\AuthenticationException;
use App\Http\Middleware\MainAccountMiddleware;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\Middleware\RoleMiddleware;
use App\Http\Middleware\CheckRegistrationComplete;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Exceptions\UnauthorizedException;
use App\Http\Middleware\VendorApiSwitchAccountMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        AuthServiceProvider::class
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'vendor.switch' => VendorApiSwitchAccountMiddleware::class,
            'localization' => Localization::class,
            'registration.complete' => CheckRegistrationComplete::class,
            'main.account' => MainAccountMiddleware::class,
        ]);

        $middleware->redirectTo(fn() => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (UnauthorizedException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'User does not have the right permissions.',
                    'status' => 403,
                ], Response::HTTP_FORBIDDEN);
            }
            return null;
        });
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'status' => 401,
                ], 401);
            }

            // fallback only if login route exists
            if (Route::has('login')) {
                return redirect()->guest(route('login'));
            }

            return response()->json([
                'message' => 'Unauthenticated and login route not found.',
                'status' => 401,
            ], 401);
        });
    })->create();
