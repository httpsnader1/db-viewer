<?php

namespace Httpsnader1\DbViewer\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class TableQueryService
{
    /**
     * Build and execute a paginated, searchable, sortable, filterable query
     * against the given table. All identifiers are sanitized via whitelisting.
     */
    public function query(
        string $table,
        array  $columns,
        array  $params,
    ): LengthAwarePaginator {
        $allowedColumns = array_column($columns, 'name');

        $search    = trim($params['search'] ?? '');
        $filters   = (array) ($params['filters'] ?? []);
        $sortCol   = $params['sort'] ?? null;
        $rawDir    = $params['direction'] ?? 'asc';
        $direction = in_array(strtolower($rawDir), ['asc', 'desc']) ? strtolower($rawDir) : 'asc';
        $perPage   = $this->safePerPage($params['perPage'] ?? null);
        $page      = max(1, (int) ($params['page'] ?? 1));

        $query = DB::table($table);

        // ─── Global Search ────────────────────────────────────────────────────
        if ($search !== '') {
            $stringCols = array_column(
                array_filter($columns, fn ($c) => in_array($c['type'], ['string', 'text', 'json'])),
                'name'
            );

            if (! empty($stringCols)) {
                $query->where(function ($q) use ($stringCols, $search) {
                    foreach ($stringCols as $col) {
                        $q->orWhere(
                            DB::raw('CAST(' . DB::connection()->getQueryGrammar()->wrap($col) . ' AS CHAR)'),
                            'LIKE',
                            "%{$search}%"
                        );
                    }
                });
            }
        }

        // ─── Advanced Filters ─────────────────────────────────────────────────
        $advanced = $params['advanced'] ?? [];
        if (is_string($advanced)) {
            $advanced = json_decode($advanced, true);
        }

        if (is_array($advanced)) {
            foreach ($advanced as $f) {
                if (!is_array($f)) continue;

                $col = $f['column']   ?? null;
                $op  = $f['operator'] ?? '=';
                $val = $f['value']    ?? null;

                // التحقق من وجود العمود (بدون تدقيق صارم في النوع)
                if (!$col || !in_array($col, $allowedColumns)) continue;
                
                // تجاهل الفلاتر التي تتطلب قيمة ولم يتم إدخالها
                if (($val === null || $val === '') && !in_array($op, ['null', 'not_null'])) continue;

                switch ($op) {
                    case '=':
                        $query->where($col, '=', $val);
                        break;
                    case '!=':
                    case '>':
                    case '<':
                    case '>=':
                    case '<=':
                        $query->where($col, $op, $val);
                        break;
                    case 'like':
                        $query->where($col, 'LIKE', "%{$val}%");
                        break;
                    case 'not_like':
                        $query->where($col, 'NOT LIKE', "%{$val}%");
                        break;
                    case 'starts_with':
                        $query->where($col, 'LIKE', "{$val}%");
                        break;
                    case 'ends_with':
                        $query->where($col, 'LIKE', "%{$val}");
                        break;
                    case 'null':
                        $query->whereNull($col);
                        break;
                    case 'not_null':
                        $query->whereNotNull($col);
                        break;
                }
            }
        }

        // ─── Sorting ──────────────────────────────────────────────────────────
        if ($sortCol && in_array($sortCol, $allowedColumns, true)) {
            $query->orderBy($sortCol, $direction);
        }

        // ─── Pagination ───────────────────────────────────────────────────────
        return $query->paginate(
            perPage: $perPage,
            page:    $page,
        );
    }

    /** Fetch a single row by its primary key value. */
    public function findRow(string $table, string $pkCol, mixed $pkValue): ?object
    {
        return DB::table($table)
            ->where($pkCol, $pkValue)
            ->first();
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    protected function safePerPage(mixed $raw): int
    {
        $allowed = config('db-viewer.per_page_options', [10, 25, 50, 100]);
        $val     = (int) ($raw ?? config('db-viewer.default_per_page', 25));
        return in_array($val, $allowed) ? $val : (int) $allowed[1] ?? 25;
    }
}
