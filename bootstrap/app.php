<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\ResellerAuth;
use App\Http\Middleware\ModeratorAuth;
use App\Http\Middleware\SupplierAuth;
use App\Http\Middleware\TrackUserActivity;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            TrackUserActivity::class,
            \App\Http\Middleware\CheckBlockedIp::class,
        ]);

        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'branch.access'      => \App\Http\Middleware\BranchAccess::class,
            'admin.access'       => \App\Http\Middleware\AdminAccess::class,
            'reseller.auth'      => ResellerAuth::class,
            'moderator.auth'     => ModeratorAuth::class,
            'supplier.auth'      => SupplierAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
        // Handle PostTooLargeException gracefully without out-of-memory crash
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'আপলোড করা ফাইল বা ডেটার সাইজ অনুমোদিত সীমার চেয়ে বেশি (Post Too Large)।'], 413);
            }
            return redirect()->back()->with('error', 'আপলোড করা ফাইলের সাইজ অনেক বড়। অনুগ্রহ করে ছোট সাইজের ছবি আপলোড করুন।');
        });
        // Redirect unauthenticated web requests to login
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return redirect()->route('login');
            }
        });
    })->create();
