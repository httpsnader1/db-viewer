# DB Viewer – Laravel Package

> A professional **Database Explorer** for Laravel applications built with **Inertia.js + Vue 3 + Tailwind CSS 4**.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/httpsnader1/db-viewer.svg)](https://packagist.org/packages/httpsnader1/db-viewer)
[![License](https://img.shields.io/github/license/httpsnader1/db-viewer)](LICENSE)

---

## Features

- 📊 **Dashboard** – database stats, row distribution chart
- 📋 **Tables List** – searchable grid of all tables with row counts
- 🔍 **DataTable** – global search, per-column filters (text/number/date range/boolean), sortable columns, pagination
- 👁️ **Column Visibility** – show/hide columns with localStorage persistence
- 🪟 **Row Details Modal** – formatted JSON, booleans, null, dates
- 🔐 **Authorization** – `Gate::define('viewDbViewer')` with easy override
- ⚙️ **Config** – include/exclude tables, cache duration, per-page options
- 🗄️ **Multi-driver** – MySQL & PostgreSQL support

---

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP         | ^8.2    |
| Laravel     | ^11 or ^12 |
| Inertia     | ^2.0    |

---

## Installation

### 1. Install via Composer

```bash
composer require httpsnader1/db-viewer
```

> **Using a local path repo (dev)?** Add to your host app's `composer.json`:
> ```json
> "repositories": [
>   {
>     "type": "path",
>     "url": "../db-viewer"
>   }
> ]
> ```
> Then: `composer require httpsnader1/db-viewer @dev`

---

### 2. Publish Config

```bash
php artisan vendor:publish --tag=db-viewer-config
```

This creates `config/db-viewer.php` in your application.

---

### 3. Publish Vue Assets

```bash
php artisan vendor:publish --tag=db-viewer-assets
```

Assets are copied to `resources/js/vendor/db-viewer/`.

---

### 4. Register Pages in Vite

In your `vite.config.js`, add the package pages to the Inertia resolver:

```js
resolve: (name) =>
  resolvePageComponent(
    `./Pages/${name}.vue`,
    {
      ...import.meta.glob('./Pages/**/*.vue'),
      ...import.meta.glob('./vendor/db-viewer/**/*.vue'),
    }
  ),
```

Or add an alias:
```js
resolve: {
  alias: {
    '@db-viewer': resolve(__dirname, 'resources/js/vendor/db-viewer'),
  },
},
```

---

### 5. Install Heroicons (required for icons)

```bash
npm install @heroicons/vue
```

---

### 6. Install Ziggy (named routes in JS)

```bash
composer require tightenco/ziggy
php artisan ziggy:generate
```

Add `@routes` to your Blade layout and `ZiggyVue` to `app.js`.

---

### 7. Set Up Inertia Root View

The package uses Inertia, so your application must have Inertia set up.
The package will use the host app's `resources/views/app.blade.php` by default.

---

## Configuration

`config/db-viewer.php`:

```php
return [
    'enabled'        => true,               // enable/disable entire package
    'path'           => 'db-explorer',       // URL prefix
    'middleware'     => ['web'],             // base middleware

    // Authorization
    'auth_callback'  => null,               // callable or null
    'allowed_emails' => [],                 // allowed in production

    // Table whitelist/blacklist
    'include_tables' => [],                 // if set, only these are shown
    'exclude_tables' => ['migrations', ...],

    // Performance
    'cache_duration' => 300,               // seconds (0 = disabled)

    // UI
    'per_page_options'      => [10, 25, 50, 100],
    'default_per_page'      => 25,
    'dashboard_top_tables'  => 10,
];
```

---

## Authorization

### Default Behavior

- **Local environment** → always accessible
- **Other environments** → checks `allowed_emails` config

### Custom Gate (Recommended)

In your `AppServiceProvider::boot()`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewDbViewer', function ($user) {
    return $user->hasRole('admin');
});
```

### Custom Callback via Config

```php
// config/db-viewer.php
'auth_callback' => function ($user) {
    return $user?->is_admin === true;
},
```

---

## Routes

| Method | URL                               | Name                    |
|--------|-----------------------------------|-------------------------|
| GET    | `/db-explorer`                    | `db-viewer.dashboard`   |
| GET    | `/db-explorer/tables`             | `db-viewer.tables`      |
| GET    | `/db-explorer/tables/{table}`     | `db-viewer.tables.show` |
| GET    | `/db-explorer/tables/{table}/row` | `db-viewer.tables.row`  |

---

## Security Notes

- All table/column names are **whitelisted** against the schema – no raw string injection possible
- Table names are validated against the allowed list before any query executes
- Column filters use parameterized queries (no raw SQL string interpolation)
- The `Gate::define('viewDbViewer')` check runs on every request via middleware

---

## Extensibility

- **Override the Gate** in `AppServiceProvider` for custom auth logic
- **Exclude sensitive tables** via `exclude_tables` config
- **Cache metadata** to reduce DB introspection calls on each request
- **Column preferences** stored in `localStorage` by default; set `column_prefs_storage = 'database'` to enable DB storage (requires custom migration – planned feature)

---

## Development / Local Setup

```bash
# Clone the repo
git clone https://github.com/httpsnader1/db-viewer.git
cd db-viewer

# Install PHP deps
composer install

# Install JS deps
npm install

# Copy .env and configure your database
cp .env.example .env
php artisan key:generate

# Run migrations (for the demo app)
php artisan migrate --seed

# Start dev server
php artisan serve
npm run dev
```

---

## Screenshots

| Dashboard | Tables | Table View |
|-----------|--------|------------|
| Stats, row distribution chart | Searchable grid of tables | DataTable with search, filters, sort, pagination |

---

## License

MIT © [Mohamed Nader](https://github.com/httpsnader1)
