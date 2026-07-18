<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'email.otp' => \App\Http\Middleware\EnsureEmailOtpVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        // 301 зі старого ЧПУ товару на актуальний (після зміни slug), щоб старі
        // посилання не отримували 404. Спрацьовує лише коли товар не знайдено
        // (Laravel уже конвертує ModelNotFoundException у NotFoundHttpException).
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if (! $request->is('product/*')) {
                return null;
            }

            $redirect = \App\Models\ProductSlugRedirect::where('old_slug', $request->segment(2))->first();
            $target = $redirect?->product;

            return $target && $target->is_active
                ? redirect()->route('product', $target->slug, 301)
                : null;
        });
    })->create();
