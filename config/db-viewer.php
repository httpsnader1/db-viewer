<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable / Disable the DB Viewer
    |--------------------------------------------------------------------------
    | Set to false to completely disable all routes and UI.
    */
    'enabled' => env('DB_VIEWER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Route Prefix & Middleware
    |--------------------------------------------------------------------------
    */
    'path'       => 'db-viewer',
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    | The Gate "viewDbViewer" is defined by the package.
    | You can override it in your AppServiceProvider or provide:
    |   - auth_callback: a PHP callable
    |   - allowed_emails: list of emails allowed in production
    */
    'auth_callback'  => null,
    'allowed_emails' => [],
    'password'       => env('DB_VIEWER_PASSWORD', 'password'), // الباسورد الافتراضي


    /*
    |--------------------------------------------------------------------------
    | Table Whitelist / Blacklist
    |--------------------------------------------------------------------------
    | include_tables: if not empty, ONLY these tables are shown.
    | exclude_tables: tables to always hide (merged with defaults).
    */
    'include_tables' => [],

    'exclude_tables' => [
        'migrations',
        'password_reset_tokens',
        'password_resets',
        'failed_jobs',
        'personal_access_tokens',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
    ],

    /*
    |--------------------------------------------------------------------------
    | Metadata Cache
    |--------------------------------------------------------------------------
    | Duration in seconds to cache table/column metadata. 0 = no cache.
    */
    'cache_duration' => env('DB_VIEWER_CACHE', 300),

    /*
    |--------------------------------------------------------------------------
    | Dashboard Charts
    |--------------------------------------------------------------------------
    | Define tables and columns for time-series charts on the dashboard.
    */
    'dashboard_charts' => [
        [
            'table' => 'reservations',
            'column' => 'created_at',
            'label' => 'Reservations',
            'color' => '#8b5cf6', // Violet
        ],
        [
            'table' => 'transactions',
            'column' => 'created_at',
            'label' => 'Transactions',
            'color' => '#10b981', // Emerald
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-Page Options
    |--------------------------------------------------------------------------
    */
    'per_page_options' => [10, 25, 50, 100],
    'default_per_page' => 25,

    /*
    |--------------------------------------------------------------------------
    | Column Preferences Storage
    |--------------------------------------------------------------------------
    | 'localStorage' (default) or 'database' (requires running migration)
    */
    'column_prefs_storage' => 'localStorage',

    /*
    |--------------------------------------------------------------------------
    | Max Rows for Stats Dashboard
    |--------------------------------------------------------------------------
    | How many top tables to show on the dashboard.
    */
    'dashboard_top_tables' => 10,

];
