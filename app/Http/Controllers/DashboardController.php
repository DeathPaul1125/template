<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    private const LAYOUT_FILE   = 'dashboard-layout.json';
    private const CRUD_META_DIR = 'crud-generator';

    public function index()
    {
        $layout = $this->loadLayout();
        $models = $this->getAvailableModels();
        return view('dashboard', compact('layout', 'models'));
    }

    public function saveLayout(Request $request)
    {
        $request->validate(['widgets' => ['present', 'array']]);
        $this->persistLayout($request->input('widgets', []));
        return response()->json(['ok' => true]);
    }

    public function widgetData(Request $request)
    {
        $type  = $request->input('type');
        $model = $request->input('model');
        $field = $request->input('field');
        $op    = $request->input('operation', 'count');
        $limit = min((int) $request->input('limit', 10), 100);

        $table = $this->resolveTable($model);
        if (!$table) {
            return response()->json(['error' => "Modelo '{$model}' no encontrado."], 404);
        }

        if (!Schema::hasTable($table)) {
            return response()->json(['error' => "Tabla '{$table}' no existe. Ejecuta: php artisan migrate"], 422);
        }

        try {
            return match ($type) {
                'stat'         => $this->statData($table, $field, $op),
                'recent-table' => $this->tableData($model, $table, $limit),
                'bar-chart',
                'pie-chart'    => $this->groupData($table, $field),
                'line-chart'   => $this->lineData($table),
                default        => response()->json(['error' => "Tipo '{$type}' desconocido"], 422),
            };
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // ── Private data helpers ─────────────────────────────────────────────────

    private function statData(string $table, ?string $field, string $op)
    {
        $value = match ($op) {
            'sum'   => number_format((float) DB::table($table)->sum($field ?: 'id'), 2),
            'avg'   => number_format((float) DB::table($table)->avg($field ?: 'id'), 2),
            'max'   => DB::table($table)->max($field ?: 'id') ?? 0,
            'min'   => DB::table($table)->min($field ?: 'id') ?? 0,
            default => DB::table($table)->count(),
        };
        return response()->json(['value' => $value]);
    }

    private function tableData(string $model, string $table, int $limit)
    {
        $meta   = $this->loadCrudMeta($model);
        $fields = $meta ? collect($meta['fields'])->pluck('name')->take(6)->toArray() : [];

        $select = array_unique(array_merge(['id'], $fields));
        if (Schema::hasColumn($table, 'created_at')) {
            $select[] = 'created_at';
        }

        // Keep only existing columns
        $select = array_values(array_filter($select, fn($c) => Schema::hasColumn($table, $c)));

        $records = DB::table($table)->latest('id')->take($limit)->get($select);
        return response()->json(['records' => $records, 'columns' => $select]);
    }

    private function groupData(string $table, ?string $field)
    {
        if (!$field || !Schema::hasColumn($table, $field)) {
            return response()->json(['error' => "Campo '{$field}' no existe en '{$table}'."], 422);
        }

        $data = DB::table($table)
            ->selectRaw("`{$field}`, COUNT(*) as total")
            ->groupBy($field)
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        return response()->json([
            'labels' => $data->pluck($field)->map(fn($v) => $v ?? '(vacío)')->toArray(),
            'values' => $data->pluck('total')->toArray(),
        ]);
    }

    private function lineData(string $table)
    {
        if (!Schema::hasColumn($table, 'created_at')) {
            return response()->json(['error' => "'{$table}' no tiene columna created_at."], 422);
        }

        $data = DB::table($table)
            ->selectRaw("DATE(created_at) as fecha, COUNT(*) as total")
            ->whereNotNull('created_at')
            ->groupByRaw("DATE(created_at)")
            ->orderBy('fecha')
            ->take(60)
            ->get();

        return response()->json([
            'labels' => $data->pluck('fecha')->toArray(),
            'values' => $data->pluck('total')->toArray(),
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function resolveTable(string $model): ?string
    {
        $builtins = [
            'User'       => 'users',
            'Role'       => 'roles',
            'Permission' => 'permissions',
        ];
        if (isset($builtins[$model])) return $builtins[$model];

        $meta = $this->loadCrudMeta($model);
        return $meta['table_name'] ?? null;
    }

    private function getAvailableModels(): array
    {
        $builtins = [
            [
                'model'  => 'User',
                'label'  => 'Usuarios',
                'table'  => 'users',
                'fields' => ['name', 'email', 'created_at'],
                'icon'   => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
            ],
            [
                'model'  => 'Role',
                'label'  => 'Roles',
                'table'  => 'roles',
                'fields' => ['name', 'guard_name'],
                'icon'   => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
            ],
            [
                'model'  => 'Permission',
                'label'  => 'Permisos',
                'table'  => 'permissions',
                'fields' => ['name', 'guard_name'],
                'icon'   => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z',
            ],
        ];

        $dir       = storage_path('app/' . self::CRUD_META_DIR);
        $generated = [];

        if (File::isDirectory($dir)) {
            $generated = collect(File::files($dir))
                ->filter(fn($f) => $f->getExtension() === 'json')
                ->map(function ($f) {
                    $meta = json_decode(File::get($f->getPathname()), true);
                    if (!$meta) return null;
                    return [
                        'model'  => $meta['model'],
                        'label'  => $meta['menu_label'] ?? $meta['model'],
                        'table'  => $meta['table_name'],
                        'fields' => collect($meta['fields'] ?? [])->pluck('name')->toArray(),
                        'icon'   => $meta['menu_icon'] ?? 'M4 6h16M4 10h16M4 14h16M4 18h16',
                    ];
                })
                ->filter()
                ->values()
                ->toArray();
        }

        return array_merge($builtins, $generated);
    }

    private function loadLayout(): array
    {
        $path = storage_path('app/' . self::LAYOUT_FILE);
        if (!File::exists($path)) return [];
        return json_decode(File::get($path), true) ?? [];
    }

    private function persistLayout(array $widgets): void
    {
        File::put(
            storage_path('app/' . self::LAYOUT_FILE),
            json_encode($widgets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    private function loadCrudMeta(string $model): ?array
    {
        $path = storage_path('app/' . self::CRUD_META_DIR . '/' . $model . '.json');
        if (!File::exists($path)) return null;
        return json_decode(File::get($path), true);
    }
}
