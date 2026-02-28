<?php

namespace Httpsnader1\DbViewer;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Httpsnader1\DbViewer\Http\Middleware\DbViewerMiddleware;

class DbViewerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge default config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/db-viewer.php',
            'db-viewer'
        );

        // Register services as singletons
        $this->app->singleton(
            \Httpsnader1\DbViewer\Services\DatabaseIntrospector::class
        );
        $this->app->singleton(
            \Httpsnader1\DbViewer\Services\TableQueryService::class
        );
    }

    public function boot(): void
    {
        // ─── Publishing ───────────────────────────────────────────────────────────
        if ($this->app->runningInConsole()) {
            // publish config
            $this->publishes([
                __DIR__ . '/../config/db-viewer.php' => config_path('db-viewer.php'),
            ], 'db-viewer-config');

            // publish Vue components / pages
            $this->publishes([
                __DIR__ . '/../resources/js' => resource_path('js/vendor/db-viewer'),
            ], 'db-viewer-assets');

            // publish translations
            $this->publishes([
                __DIR__ . '/../lang' => lang_path('vendor/db-viewer'),
            ], 'db-viewer-lang');
        }

        // ─── Translations ────────────────────────────────────────────────────────
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'db-viewer');

        // ─── Routes ───────────────────────────────────────────────────────────────
        $this->loadRoutes();

        // ─── Gate ─────────────────────────────────────────────────────────────────
        $this->registerGate();
    }

    protected function loadRoutes(): void
    {
        if (! config('db-viewer.enabled', true)) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    protected function registerGate(): void
    {
        // Default gate: only allow in local env unless a custom callback is set.
        // Override in AppServiceProvider: Gate::define('viewDbViewer', fn() => true);
        Gate::define('viewDbViewer', function ($user = null) {
            $authCallback = config('db-viewer.auth_callback');

            if ($authCallback && is_callable($authCallback)) {
                return $authCallback($user);
            }

            // Default: allow in local, require admin email in production
            if (app()->environment('local')) {
                return true;
            }

            $allowedEmails = config('db-viewer.allowed_emails', []);
            if ($user && in_array($user->email, $allowedEmails)) {
                return true;
            }

            return false;
        });
    }
}
