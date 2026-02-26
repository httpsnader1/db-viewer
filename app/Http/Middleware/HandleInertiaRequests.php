<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     */
    public function share(Request $request): array
    {
        // Only fetch tables if the user is authenticated for the viewer
        $tables = [];
        if (session()->has('db_viewer_authenticated')) {
            $introspector = app(\Httpsnader1\DbViewer\Services\DatabaseIntrospector::class);
            $tables = collect($introspector->getTables())->map(fn($t) => [
                'name' => $t,
                'row_count' => $introspector->getRowCount($t)
            ])->values()->toArray();
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'db_viewer' => [
                'tables' => $tables,
                'dbName' => \Illuminate\Support\Facades\DB::getDatabaseName(),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ]);
    }
}
