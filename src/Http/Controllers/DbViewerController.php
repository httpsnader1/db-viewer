<?php

namespace Httpsnader1\DbViewer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Httpsnader1\DbViewer\Services\DatabaseIntrospector;
use Httpsnader1\DbViewer\Services\TableQueryService;

class DbViewerController extends Controller
{
    public function __construct(
        protected DatabaseIntrospector $introspector,
        protected TableQueryService    $queryService,
    ) {}

    // ─── Authentication ────────────────────────────────────────────────────────
    
    public function loginForm(): InertiaResponse
    {
        if (session()->has('db_viewer_authenticated')) {
            return redirect()->route('db-viewer.dashboard');
        }

        return Inertia::render('DbViewer/Login');
    }

    public function authenticate(Request $request): \Illuminate\Http\RedirectResponse
    {
        $password = config('db-viewer.password');
        
        if ($request->password === $password) {
            session()->put('db_viewer_authenticated', true);
            return redirect()->route('db-viewer.dashboard');
        }

        return back()->withErrors(['password' => 'Incorrect password.']);
    }

    public function logout(): \Illuminate\Http\RedirectResponse
    {
        session()->forget('db_viewer_authenticated');
        return redirect()->route('db-viewer.login');
    }

    // ─── Dashboard ─────────────────────────────────────────────────────────────


    public function dashboard(Request $request): InertiaResponse
    {
        $startDate = $request->query('startDate') ?: now()->startOfMonth()->toDateString();
        $endDate = $request->query('endDate') ?: now()->endOfMonth()->toDateString();
        
        $stats = $this->introspector->getDashboardStats($startDate, $endDate);

        return Inertia::render('DbViewer/Dashboard', [
            'stats' => $stats,
        ]);
    }

    // ─── Tables List ───────────────────────────────────────────────────────────

    public function tables(): InertiaResponse
    {
        $tables = collect($this->introspector->getTables())
            ->map(fn ($t) => [
                'name'      => $t,
                'row_count' => $this->introspector->getRowCount($t),
            ])
            ->values()
            ->toArray();

        return Inertia::render('DbViewer/Tables', [
            'tables' => $tables,
            'dbName' => \Illuminate\Support\Facades\DB::getDatabaseName(),
        ]);
    }

    // ─── Table Data View ───────────────────────────────────────────────────────

    public function show(Request $request, string $table): InertiaResponse
    {
        $this->validateTable($table);

        $columns = $this->introspector->getColumns($table);
        $pk      = $this->introspector->getPrimaryKey($table);

        $params = $request->only([
            'search', 'filters', 'sort', 'direction', 'page', 'perPage', 'advanced',
        ]);

        $paginator = $this->queryService->query($table, $columns, $params);

        $rows = collect($paginator->items())
            ->map(fn ($row) => (array) $row)
            ->toArray();

        return Inertia::render('DbViewer/TableView', [
            'table'      => $table,
            'columns'    => $columns,
            'primaryKey' => $pk,
            'rows'       => $rows,
            'pagination' => [
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
            'filters' => [
                'search'    => $params['search']    ?? '',
                'filters'   => $params['filters']   ?? [],
                'sort'      => $params['sort']      ?? null,
                'direction' => $params['direction'] ?? 'asc',
                'perPage'   => $paginator->perPage(),
                'page'      => $paginator->currentPage(),
                'advanced'  => $params['advanced'] ?? null,
            ],
            'perPageOptions' => config('db-viewer.per_page_options', [10, 25, 50, 100]),
            'dbName'         => \Illuminate\Support\Facades\DB::getDatabaseName(),
        ]);
    }

    // ─── Single Row ────────────────────────────────────────────────────────────

    public function row(Request $request, string $table): \Illuminate\Http\JsonResponse
    {
        $this->validateTable($table);

        $pk  = $this->introspector->getPrimaryKey($table);
        $val = $request->query('pk_value');

        if (! $pk || $val === null) {
            return response()->json(['error' => 'No primary key available'], 400);
        }

        $row = $this->queryService->findRow($table, $pk, $val);

        if (! $row) {
            return response()->json(['error' => 'Row not found'], 404);
        }

        return response()->json(['row' => (array) $row]);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    protected function validateTable(string $table): void
    {
        $allowed = $this->introspector->getTables();

        if (! in_array($table, $allowed, true)) {
            abort(404, "Table [{$table}] not found or not allowed.");
        }
    }
}
