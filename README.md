# DB Viewer – Laravel Package

> A professional **Database Explorer** for Laravel applications built with **Inertia.js + Vue 3 + Tailwind CSS 4**.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/httpsnader1/db-viewer.svg)](https://packagist.org/packages/httpsnader1/db-viewer)
[![License](https://img.shields.io/github/license/httpsnader1/db-viewer)](LICENSE)

---

## 🌟 Features

- 📊 **Interactive Dashboard** – View database stats, total records, and dynamic time-series charts using **ApexCharts**.
- 📅 **Date Range Filtering** – Advanced chronological filtering via **Vue DatePicker** to instantly adjust dashboard charts.
- 📋 **Tables List** – Searchable grid of all tables, automatically sorted by row counts for quick insights.
- 🔍 **Advanced DataTable** – Global search, advanced per-column filters (text, number, date range, boolean), sortable columns, and pagination.
- 👁️ **Column Visibility** – Dynamically show or hide columns with localStorage persistence.
- 🪟 **Row Details Modal** – Clearly formatted JSON, booleans, nulls, and dates for deep inspection.
- 🔐 **Built-in Authentication** – Secure your database view with a configurable password or `Gate::define('viewDbViewer')` with easy overrides.
- ⚙️ **Highly Configurable** – Manage visible tables, chart metrics, cache durations, and pagination options via a central config file.
- 🗄️ **Multi-driver Support** – Fully compatible with MySQL, PostgreSQL, and SQLite.

---

## 📋 Requirements

| Requirement | Version |
|-------------|---------|
| PHP         | ^8.2    |
| Laravel     | ^11 or ^12 |
| Inertia     | ^2.0    |
| Vue         | ^3.0    |

---

## 🚀 Installation

### 1. Install via Composer

```bash
composer require httpsnader1/db-viewer
```

### 2. Publish Config & Assets

```bash
# Publish Configuration File
php artisan vendor:publish --tag=db-viewer-config

# Publish Vue Assets
php artisan vendor:publish --tag=db-viewer-assets
```

This creates `config/db-viewer.php` and copies the Vue components to `resources/js/vendor/db-viewer/`.

### 3. Install NPM Dependencies

DB Viewer uses several frontend packages to deliver a premium experience. Install them via your package manager:

```bash
npm install @heroicons/vue vue3-apexcharts apexcharts @vuepic/vue-datepicker
```

### 4. Register Pages in Vite

In your host application's `vite.config.js`, configure the Inertia plugin to resolve the package's pages:

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            // Optional but recommended alias
            '@db-viewer': '/resources/js/vendor/db-viewer',
        },
    },
});
```

And in your `app.js` (where you resolve Inertia pages):

```js
resolve: (name) => {
    const pages = {
        ...import.meta.glob('./Pages/**/*.vue', { eager: true }),
        ...import.meta.glob('./vendor/db-viewer/Pages/**/*.vue', { eager: true })
    };
    
    // Check if the route is a DB Viewer route
    if (name.startsWith('DbViewer/')) {
        return pages[`./vendor/db-viewer/Pages/${name.replace('DbViewer/', '')}.vue`];
    }
    
    return pages[`./Pages/${name}.vue`];
},
```

### 5. Install Ziggy (For named routes in JS)

```bash
composer require tightenco/ziggy
php artisan ziggy:generate
```
*(Make sure to add `@routes` to your Blade layout and `ZiggyVue` to your `app.js` setup).*

### 6. Compile Assets

```bash
npm run dev
# or npm run build
```

---

## ⚙️ Configuration

The package behavior can be customized by editing `config/db-viewer.php`:

```php
return [
    'enabled'        => true,                // Quick toggle to enable/disable the package
    'path'           => 'db-viewer',         // URL prefix (e.g., your-app.com/db-viewer)
    'password'       => 'password',          // Change this! Base password protection

    // Table whitelist/blacklist
    'include_tables' => [],                  // If populated, ONLY these tables are visible
    'exclude_tables' => ['migrations', 'password_resets', 'sessions', ...],

    // Performance
    'cache_duration' => 300,                 // Seconds to cache schema metadata (0 = disabled)

    // Dashboard Time-Series Charts
    'dashboard_charts' => [
        [
            'table' => 'users',
            'column' => 'created_at',
            'label' => 'New Users',
            'color' => '#8b5cf6', // Stylish Violet
        ],
    ],
];
```

---

## 🔐 Security & Authorization

DB Viewer uses a robust middleware stack:

1. **Password Protection:** Out-of-the-box, the package asks for a password (defined in `config/db-viewer.php`). 
2. **Laravel Gate:** You can define a custom Gate in your `AppServiceProvider` for role-based access instead of (or alongside) the password.

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewDbViewer', function ($user) {
    return $user->hasRole('super-admin');
});
```
3. **Internal Sandboxing:** DB Viewer uses safe, parameterized queries and strictly whitelists table names to prevent SQL injections.

---

## 🛣 Routes

Once installed, visit **`/db-viewer`** in your browser.

| Method | URI                                | Name                    |
|--------|------------------------------------|-------------------------|
| GET    | `/db-viewer/login`                 | `db-viewer.login`       |
| GET    | `/db-viewer`                       | `db-viewer.dashboard`   |
| GET    | `/db-viewer/tables`                | `db-viewer.tables`      |
| GET    | `/db-viewer/tables/{table}`        | `db-viewer.tables.show` |
| GET    | `/db-viewer/tables/{table}/row`    | `db-viewer.tables.row`  |

---

## 📄 License

The MIT License (MIT). Created by [Mohamed Nader](https://github.com/httpsnader1).
