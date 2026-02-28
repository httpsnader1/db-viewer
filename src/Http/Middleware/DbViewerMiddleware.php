<?php

namespace Httpsnader1\DbViewer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class DbViewerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Is the package enabled?
        if (! config('db-viewer.enabled', true)) {
            abort(404);
        }


        // 1. Skip check for login routes
        $loginRoutes = [
            route('db-viewer.login', [], false),
            route('db-viewer.authenticate', [], false)
        ];
        
        if (in_array('/' . ltrim($request->getPathInfo(), '/'), $loginRoutes)) {
            return $next($request);
        }

        // 2. Check Password Session (If password is set in config)
        $configPassword = config('db-viewer.password');
        if ($configPassword && ! session()->has('db_viewer_authenticated')) {
            return redirect()->route('db-viewer.login');
        }

        // 3. Check the Gate (Optional second layer)
        if (Gate::has('viewDbViewer') && ! Gate::allows('viewDbViewer')) {
            // Only abort if user IS logged in but doesn't have gate permission
            // Or skip gate if only using password
            if (! $configPassword) {
                abort(403, trans('db-viewer::unauthorized_access'));
            }
        }

        // 4. Share Inertia Data
        if (class_exists(\Inertia\Inertia::class)) {
            $tables = [];
            if (session()->has('db_viewer_authenticated') || !config('db-viewer.password')) {
                $introspector = app(\Httpsnader1\DbViewer\Services\DatabaseIntrospector::class);
                $tables = collect($introspector->getTables())->map(fn($t) => [
                    'name' => $t,
                    'row_count' => $introspector->getRowCount($t)
                ])->values()->toArray();
            }

            $locale = app()->getLocale();
            $packageLangPath = __DIR__ . '/../../../lang';
            $appLangPath     = function_exists('lang_path') ? lang_path('vendor/db-viewer') : base_path('lang/vendor/db-viewer');
            
            $translations = [];
            
            // 1. Load English fallback from package
            if (file_exists("$packageLangPath/en.json")) {
                $translations = json_decode(file_get_contents("$packageLangPath/en.json"), true) ?? [];
            }
            
            // 2. Load English from app (override)
            if (file_exists("$appLangPath/en.json")) {
                $appEn = json_decode(file_get_contents("$appLangPath/en.json"), true) ?? [];
                $translations = array_merge($translations, $appEn);
            }
            
            // 3. Load current locale from package
            if ($locale !== 'en' && file_exists("$packageLangPath/$locale.json")) {
                $currentTrans = json_decode(file_get_contents("$packageLangPath/$locale.json"), true) ?? [];
                $translations = array_merge($translations, $currentTrans);
            }

            // 4. Load current locale from app (override)
            if ($locale !== 'en' && file_exists("$appLangPath/$locale.json")) {
                $appLocale = json_decode(file_get_contents("$appLangPath/$locale.json"), true) ?? [];
                $translations = array_merge($translations, $appLocale);
            }

            \Inertia\Inertia::share([
                'db_viewer' => [
                    'tables' => $tables,
                    'dbName' => \Illuminate\Support\Facades\DB::getDatabaseName(),
                    'translations' => $translations,
                    'locale' => $locale,
                ],
            ]);
        }

        return $next($request);
    }
}

