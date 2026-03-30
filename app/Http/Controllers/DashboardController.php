<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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

        $modelInfo = $this->resolveModel($model);
        if (!$modelInfo) {
            return response()->json(['error' => "Modelo '{$model}' no encontrado."], 404);
        }

        $table = $modelInfo['table'];
        if (!Schema::hasTable($table)) {
            return response()->json(['error' => "Tabla '{$table}' no existe. Ejecuta: php artisan migrate"], 422);
        }

        try {
            return match ($type) {
                'stat'         => $this->statData($table, $field, $op),
                'growth-stat'  => $this->growthData($table, $field, $op),
                'recent-table' => $this->tableData($modelInfo, $limit),
                'bar-chart',
                'pie-chart'    => $this->groupData($table, $field),
                'line-chart'   => $this->lineData($table),
                'top-list'     => $this->topData($table, $field, $limit),
                default        => response()->json(['error' => "Tipo '{$type}' desconocido"], 422),
            };
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // ── Private data helpers ─────────────────────────────────────────────────

    /**
     * Devuelve un query builder base que excluye registros con soft-delete.
     */
    private function baseQuery(string $table)
    {
        $q = DB::table($table);
        if (Schema::hasColumn($table, 'deleted_at')) {
            $q->whereNull('deleted_at');
        }
        return $q;
    }

    private function statData(string $table, ?string $field, string $op)
    {
        $value = match ($op) {
            'sum'   => number_format((float) $this->baseQuery($table)->sum($field ?: 'id'), 2),
            'avg'   => number_format((float) $this->baseQuery($table)->avg($field ?: 'id'), 2),
            'max'   => $this->baseQuery($table)->max($field ?: 'id') ?? 0,
            'min'   => $this->baseQuery($table)->min($field ?: 'id') ?? 0,
            default => number_format($this->baseQuery($table)->count()),
        };
        return response()->json(['value' => $value]);
    }

    private function growthData(string $table, ?string $field, string $op)
    {
        if (!Schema::hasColumn($table, 'created_at')) {
            $value = number_format(DB::table($table)->count());
            return response()->json(['value' => $value, 'change' => null, 'trend' => null, 'current' => null, 'previous' => null]);
        }

        $thisStart = now()->startOfMonth();
        $prevStart = now()->subMonth()->startOfMonth();
        $prevEnd   = now()->subMonth()->endOfMonth();
        $colExpr   = ($field && Schema::hasColumn($table, $field)) ? $field : 'id';

        $compute = function ($query) use ($op, $colExpr) {
            return match ($op) {
                'sum'   => (float) $query->sum($colExpr),
                'avg'   => (float) $query->avg($colExpr),
                default => (int)   $query->count(),
            };
        };

        $total    = $compute($this->baseQuery($table));
        $current  = $compute($this->baseQuery($table)->where('created_at', '>=', $thisStart));
        $previous = $compute($this->baseQuery($table)->whereBetween('created_at', [$prevStart, $prevEnd]));

        $change = null;
        $trend  = null;
        if ($previous > 0) {
            $change = round((($current - $previous) / $previous) * 100, 1);
            $trend  = $change >= 0 ? 'up' : 'down';
        } elseif ($current > 0) {
            $change = 100.0;
            $trend  = 'up';
        }

        return response()->json([
            'value'    => number_format($total),
            'current'  => number_format($current),
            'previous' => number_format($previous),
            'change'   => $change,
            'trend'    => $trend,
        ]);
    }

    private function tableData(array $modelInfo, int $limit)
    {
        $table    = $modelInfo['table'];
        $fields   = $modelInfo['fields'] ?? [];
        $skipCols = ['password','remember_token','email_verified_at','two_factor_secret',
                     'two_factor_recovery_codes','two_factor_confirmed_at'];

        $displayFields = collect($fields)
            ->filter(fn($f) => !in_array($f['name'], $skipCols))
            ->take(6)
            ->toArray();

        $select = array_unique(array_merge(['id'], collect($displayFields)->pluck('name')->toArray()));
        if (Schema::hasColumn($table, 'created_at')) {
            $select[] = 'created_at';
        }
        $select = array_values(array_filter($select, fn($c) => Schema::hasColumn($table, $c)));

        $records = $this->baseQuery($table)->latest('id')->take($limit)->get($select);
        $typeMap  = collect($fields)->pluck('type', 'name')->toArray();

        return response()->json([
            'records' => $records,
            'columns' => $select,
            'typeMap' => $typeMap,
        ]);
    }

    private function groupData(string $table, ?string $field)
    {
        if (!$field || !Schema::hasColumn($table, $field)) {
            return response()->json(['error' => "Campo '{$field}' no existe en '{$table}'."], 422);
        }

        $data = $this->baseQuery($table)
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

        $data = $this->baseQuery($table)
            ->selectRaw("DATE(created_at) as fecha, COUNT(*) as total")
            ->whereNotNull('created_at')
            ->where('created_at', '>=', now()->subDays(60))
            ->groupByRaw("DATE(created_at)")
            ->orderBy('fecha')
            ->get();

        return response()->json([
            'labels' => $data->pluck('fecha')->toArray(),
            'values' => $data->pluck('total')->toArray(),
        ]);
    }

    private function topData(string $table, ?string $field, int $limit)
    {
        if (!$field || !Schema::hasColumn($table, $field)) {
            return response()->json(['error' => "Campo '{$field}' no válido."], 422);
        }

        $labelCol = null;
        foreach (['name', 'title', 'label', 'nombre', 'descripcion', 'description'] as $c) {
            if (Schema::hasColumn($table, $c)) { $labelCol = $c; break; }
        }

        $select  = array_unique(array_filter([$labelCol ?? 'id', $field]));
        $records = $this->baseQuery($table)->select($select)->orderByDesc($field)->limit($limit)->get();

        $labelKey = $labelCol ?? 'id';
        $items    = $records->map(fn($r) => [
            'label' => data_get($r, $labelKey) ?? '—',
            'value' => data_get($r, $field) ?? 0,
        ])->toArray();

        $max = count($items) ? max(array_column($items, 'value')) : 1;
        return response()->json(['items' => $items, 'max' => max($max, 1), 'field' => $field]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function resolveModel(string $model): ?array
    {
        return collect($this->getAvailableModels())->firstWhere('model', $model);
    }

    private function getAvailableModels(): array
    {
        // Load CRUD metas for field type info and icons
        $metas = [];
        $dir   = storage_path('app/' . self::CRUD_META_DIR);
        if (File::isDirectory($dir)) {
            foreach (File::files($dir) as $f) {
                if ($f->getExtension() !== 'json') continue;
                $meta = json_decode(File::get($f->getPathname()), true);
                if ($meta && isset($meta['model'])) $metas[$meta['model']] = $meta;
            }
        }

        $iconMap = [
            'User'       => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
            'Role'       => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
            'Permission' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z',
            'Setting'    => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        ];

        $skipCols = ['password','remember_token','email_verified_at','two_factor_secret',
                     'two_factor_recovery_codes','two_factor_confirmed_at'];

        $models = [];
        if (!File::isDirectory(app_path('Models'))) return $models;

        foreach (File::files(app_path('Models')) as $file) {
            if ($file->getExtension() !== 'php') continue;
            $modelName = $file->getFilenameWithoutExtension();
            $className = "App\\Models\\{$modelName}";
            if (!class_exists($className)) continue;

            try {
                $instance = new $className;
                if (!method_exists($instance, 'getTable')) continue;
                $table = $instance->getTable();
            } catch (\Throwable $e) {
                continue;
            }

            if (!Schema::hasTable($table)) continue;

            $meta       = $metas[$modelName] ?? null;
            $allColumns = Schema::getColumnListing($table);

            if ($meta && !empty($meta['fields'])) {
                $fields = collect($meta['fields'])
                    ->map(fn($f) => ['name' => $f['name'], 'type' => $f['type'] ?? 'string'])
                    ->toArray();
            } else {
                $fields = collect($allColumns)
                    ->filter(fn($c) => !in_array($c, array_merge(['id','created_at','updated_at','deleted_at'], $skipCols)))
                    ->map(fn($c) => ['name' => $c, 'type' => 'string'])
                    ->values()
                    ->toArray();
            }

            $models[] = [
                'model'         => $modelName,
                'label'         => $meta['menu_label'] ?? Str::headline($modelName),
                'table'         => $table,
                'fields'        => $fields,
                'icon'          => $meta['menu_icon'] ?? ($iconMap[$modelName] ?? 'M4 6h16M4 10h16M4 14h16M4 18h16'),
                'hasTimestamps' => in_array('created_at', $allColumns),
                'routeBase'     => $meta ? Str::kebab(Str::plural($modelName)) : null,
            ];
        }

        return $models;
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
}
