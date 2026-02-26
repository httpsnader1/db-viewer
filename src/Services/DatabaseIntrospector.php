<?php

namespace Httpsnader1\DbViewer\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class DatabaseIntrospector
{
    protected string $cachePrefix = 'db_viewer_meta_';
    protected int    $cacheDuration;
    protected string $driver;

    public function __construct()
    {
        $this->cacheDuration = (int) config('db-viewer.cache_duration', 300);
        $this->driver        = DB::getDriverName();
    }

    // ─── Public API ────────────────────────────────────────────────────────────

    /** Return the list of visible tables (respects include/exclude config). */
    public function getTables(): array
    {
        return $this->remember('tables', fn () => $this->fetchTables());
    }

    /** Return column metadata for a given table. */
    public function getColumns(string $table): array
    {
        return $this->remember("columns_{$table}", fn () => $this->fetchColumns($table));
    }

    /** Return row count for a table (fast via SELECT COUNT(*) or estimates). */
    public function getRowCount(string $table): int
    {
        try {
            return DB::table($table)->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /** Dashboard stats: top N tables with row counts + db-level meta. */
    public function getDashboardStats(?string $startDate = null, ?string $endDate = null): array
    {
        $tables = $this->getTables();
        $top    = config('db-viewer.dashboard_top_tables', 10);

        $allTableData = collect($tables)
            ->map(fn ($t) => [
                'name'      => $t,
                'row_count' => $this->getRowCount($t),
            ]);

        $totalRecords = $allTableData->sum('row_count');

        $tableStats = $allTableData
            ->sortByDesc('row_count')
            ->take($top)
            ->values()
            ->toArray();

        $charts = [];
        $configuredCharts = config('db-viewer.dashboard_charts', []);

        foreach ($configuredCharts as $chart) {
            if (in_array($chart['table'], $tables)) {
                $data = $this->getTimeSeriesData($chart['table'], $chart['column'], $startDate, $endDate);
                $charts[] = [
                    'label' => $chart['label'] ?? $chart['table'],
                    'color' => $chart['color'] ?? '#8b5cf6',
                    'data'  => $data,
                    'total' => array_sum(array_column($data, 'y')),
                ];
            }
        }

        return [
            'driver'        => $this->driver,
            'database'      => DB::getDatabaseName(),
            'total_tables'  => count($tables),
            'total_records' => $totalRecords,
            'table_stats'   => $tableStats,
            'charts'        => $charts,
        ];
    }

    protected function getTimeSeriesData(string $table, string $column, ?string $startDate, ?string $endDate): array
    {
        $query = DB::table($table);
        $grammar = DB::connection()->getQueryGrammar();
        $wrappedColumn = $grammar->wrap($column);

        if ($startDate) {
            $query->where($column, '>=', $startDate);
        }
        if ($endDate) {
            $query->where($column, '<=', $endDate . ' 23:59:59');
        }

        // Group by day. Driver logic:
        $groupExpression = match ($this->driver) {
            'sqlite' => "strftime('%Y-%m-%d', $wrappedColumn)",
            'mysql'  => "DATE($wrappedColumn)",
            'pgsql'  => "($wrappedColumn)::date",
            default  => "DATE($wrappedColumn)"
        };

        $results = $query->select(
            DB::raw("$groupExpression as group_date"),
            DB::raw("COUNT(*) as aggregate_count")
        )
        ->groupBy(DB::raw($groupExpression))
        ->orderBy('group_date', 'asc')
        ->get();

        return $results->map(function($item) {
            $itemArray = (array) $item;
            return [
                'x' => $itemArray['group_date'],
                'y' => (int) $itemArray['aggregate_count']
            ];
        })->toArray();
    }

    protected function getTimeSeriesDataLegacy(string $table, string $column, ?string $startDate, ?string $endDate): array
    {
        // Fallback for simple count by date if the complex one fails or for initial testing
        return [];
    }

    /** Get the primary key column name for a table (null if none detected). */
    public function getPrimaryKey(string $table): ?string
    {
        return $this->remember("pk_{$table}", function () use ($table) {
            try {
                // Laravel Schema can detect this for most drivers
                $keys = Schema::getIndexes($table);
                foreach ($keys as $index) {
                    if ($index['primary'] ?? false) {
                        return $index['columns'][0] ?? null;
                    }
                }
            } catch (\Throwable) {}

            // Fallback: look for "id" column
            $columns = array_column($this->getColumns($table), 'name');
            if (in_array('id', $columns)) {
                return 'id';
            }

            return null;
        });
    }

    // ─── Internal ──────────────────────────────────────────────────────────────

    protected function fetchTables(): array
    {
        $include = config('db-viewer.include_tables', []);
        $exclude = config('db-viewer.exclude_tables', []);
        $dbName  = DB::getDatabaseName();

        $all = collect(Schema::getTables())
            ->filter(function ($table) use ($dbName) {
                // في Laravel 11/12، يرجع Schema::getTables مصفوفة من الكائنات
                // نقوم بالتأكد أن الجدول ينتمي لقاعدة البيانات الحالية
                $schema = $table['schema'] ?? $table['database'] ?? null;
                
                // إذا لم يتوفر اسم السكيما، نفترض أنها الحالية (أو نعتمد على الاسم فقط)
                if (!$schema) return true;
                
                return $schema === $dbName;
            })
            ->pluck('name')
            ->map(fn ($t) => (string) $t)
            ->filter(fn ($t) => ! empty(trim($t)));

        if (! empty($include)) {
            $all = $all->intersect($include);
        }

        if (! empty($exclude)) {
            $all = $all->diff($exclude);
        }

        return $all->values()->toArray();
    }

    protected function fetchColumns(string $table): array
    {
        $raw = Schema::getColumns($table);

        return collect($raw)->map(function ($col) {
            return [
                'name'     => $col['name'],
                'type'     => $this->normalizeType($col['type'] ?? $col['type_name'] ?? 'string'),
                'nullable' => (bool) ($col['nullable'] ?? true),
                'default'  => $col['default'] ?? null,
            ];
        })->toArray();
    }

    protected function normalizeType(string $raw): string
    {
        $raw = strtolower($raw);

        return match (true) {
            str_contains($raw, 'int')                       => 'integer',
            str_contains($raw, 'float')
                || str_contains($raw, 'double')
                || str_contains($raw, 'decimal')
                || str_contains($raw, 'numeric')            => 'float',
            str_contains($raw, 'bool')                      => 'boolean',
            str_contains($raw, 'date')
                || str_contains($raw, 'time')               => 'datetime',
            str_contains($raw, 'json')                      => 'json',
            str_contains($raw, 'text')
                || str_contains($raw, 'varchar')
                || str_contains($raw, 'char')               => 'string',
            default                                         => 'string',
        };
    }

    private function remember(string $key, callable $callback): mixed
    {
        $fullKey = $this->cachePrefix . $key;

        if ($this->cacheDuration > 0) {
            return Cache::remember($fullKey, $this->cacheDuration, $callback);
        }

        return $callback();
    }
}
