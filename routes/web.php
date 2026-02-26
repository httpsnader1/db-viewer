<?php

use Illuminate\Support\Facades\Route;
use Httpsnader1\DbViewer\Http\Controllers\DbViewerController;
use Httpsnader1\DbViewer\Http\Middleware\DbViewerMiddleware;

$path       = config('db-viewer.path', 'db-viewer');
$middleware = array_merge(
    config('db-viewer.middleware', ['web']),
    [DbViewerMiddleware::class]
);

Route::prefix($path)
    ->middleware($middleware)
    ->name('db-viewer.')
    ->group(function () {
        // Login
        Route::get('/login', [DbViewerController::class, 'loginForm'])
            ->name('login');
        Route::post('/login', [DbViewerController::class, 'authenticate'])
            ->name('authenticate');
        Route::post('/logout', [DbViewerController::class, 'logout'])
            ->name('logout');

        // Dashboard
        Route::get('/', [DbViewerController::class, 'dashboard'])
            ->name('dashboard');


        // Tables list
        Route::get('/tables', [DbViewerController::class, 'tables'])
            ->name('tables');

        // Table data view
        Route::get('/tables/{table}', [DbViewerController::class, 'show'])
            ->name('tables.show')
            ->where('table', '[a-zA-Z0-9_]+');

        // Single row detail (by PK)
        Route::get('/tables/{table}/row', [DbViewerController::class, 'row'])
            ->name('tables.row')
            ->where('table', '[a-zA-Z0-9_]+');
    });
