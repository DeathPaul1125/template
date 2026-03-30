<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class CrudGeneratorController extends Controller
{
    private const META_DIR = 'crud-generator';

    public function index()
    {
        $saved = $this->loadAllMeta();
        return view('crud-generator.index', compact('saved'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'model_name'      => ['required', 'regex:/^[A-Z][a-zA-Z0-9]+$/'],
            'fields'          => ['required', 'array', 'min:1'],
            'fields.*.name'   => ['required', 'regex:/^[a-z][a-z0-9_]*$/'],
            'fields.*.type'   => ['required', 'in:string,text,integer,bigInteger,boolean,date,datetime,decimal,float,enum,image,file'],
            'menu_label'      => ['nullable', 'string', 'max:40'],
            'menu_icon'       => ['nullable', 'string', 'max:600'],
        ]);

        $model         = $request->model_name;
        $tableName     = $request->filled('table_name') ? $request->table_name : Str::snake(Str::plural($model));
        $fields        = $request->fields;
        $timestamps    = $request->boolean('timestamps', true);
        $softDeletes   = $request->boolean('soft_deletes');
        $useDatatables = $request->boolean('use_datatables', true);

        $generated = [];
        $genErrors = [];

        // 1. Model
        try {
            $path = app_path("Models/{$model}.php");
            File::ensureDirectoryExists(dirname($path));
            File::put($path, $this->makeModel($model, $fields, $timestamps, $softDeletes));
            $generated[] = ['file' => "app/Models/{$model}.php", 'type' => 'model'];
        } catch (\Throwable $e) {
            $genErrors[] = "Model: {$e->getMessage()}";
        }

        // 2. Migration
        try {
            $ts   = now()->format('Y_m_d_His');
            $slug = Str::snake(Str::plural($model));
            $migFile = "{$ts}_create_{$slug}_table.php";
            File::put(database_path("migrations/{$migFile}"),
                $this->makeMigration($tableName, $fields, $timestamps, $softDeletes));
            $generated[] = ['file' => "database/migrations/{$migFile}", 'type' => 'migration'];
        } catch (\Throwable $e) {
            $genErrors[] = "Migration: {$e->getMessage()}";
        }

        // 3. Controller
        try {
            $path = app_path("Http/Controllers/{$model}Controller.php");
            File::put($path, $this->makeController($model, $tableName, $fields, $useDatatables));
            $generated[] = ['file' => "app/Http/Controllers/{$model}Controller.php", 'type' => 'controller'];
        } catch (\Throwable $e) {
            $genErrors[] = "Controller: {$e->getMessage()}";
        }

        // 4. Views
        try {
            $vp = Str::kebab(Str::plural($model));
            $viewDir = resource_path("views/{$vp}");
            File::ensureDirectoryExists($viewDir);
            File::put("{$viewDir}/index.blade.php",  $this->makeIndexView($model, $fields, $useDatatables));
            File::put("{$viewDir}/create.blade.php", $this->makeCreateView($model, $fields));
            File::put("{$viewDir}/edit.blade.php",   $this->makeEditView($model, $fields));
            $generated[] = ['file' => "resources/views/{$vp}/index.blade.php",  'type' => 'view'];
            $generated[] = ['file' => "resources/views/{$vp}/create.blade.php", 'type' => 'view'];
            $generated[] = ['file' => "resources/views/{$vp}/edit.blade.php",   'type' => 'view'];
        } catch (\Throwable $e) {
            $genErrors[] = "Views: {$e->getMessage()}";
        }

        // 5. Inject routes automatically
        $addRoutes = $request->boolean('add_routes');
        if ($addRoutes) {
            try {
                $this->injectRoutes($model, $useDatatables);
                $generated[] = ['file' => 'routes/web.php', 'type' => 'route'];
            } catch (\Throwable $e) {
                $genErrors[] = "Routes: {$e->getMessage()}";
                $addRoutes = false;
            }
        }

        // 6. Inject menu item automatically
        $addToMenu = $request->boolean('add_to_menu');
        $menuLabel = trim((string) $request->menu_label) ?: Str::title(str_replace('_', ' ', Str::snake(Str::plural($model))));
        $menuIcon  = $request->menu_icon ?: 'M4 6h16M4 10h16M4 14h16M4 18h16';
        if ($addToMenu) {
            try {
                $this->injectMenuItem($model, $menuLabel, $menuIcon);
                $generated[] = ['file' => 'resources/views/layouts/app.blade.php', 'type' => 'menu'];
            } catch (\Throwable $e) {
                $genErrors[] = "Menu: {$e->getMessage()}";
            }
        }

        // 7. Save metadata for future edits / rebuild
        $this->saveMeta($model, [
            'model'         => $model,
            'table_name'    => $tableName,
            'fields'        => $fields,
            'timestamps'    => $timestamps,
            'soft_deletes'  => $softDeletes,
            'use_datatables'=> $useDatatables,
            'add_routes'    => $addRoutes,
            'add_to_menu'   => $addToMenu,
            'menu_label'    => $menuLabel,
            'menu_icon'     => $menuIcon,
            'generated_at'  => now()->toDateTimeString(),
        ]);

        return back()
            ->with('generated', $generated)
            ->with('route_snippet', $addRoutes ? null : $this->makeRouteSnippet($model, $useDatatables))
            ->with('gen_errors', $genErrors)
            ->with('gen_model', $model);
    }

    public function icons()
    {
        return view('crud-generator.icons');
    }

    public function edit(string $model)
    {
        $meta = $this->loadMeta($model);
        abort_unless($meta, 404);
        $saved = $this->loadAllMeta();
        return view('crud-generator.index', compact('saved', 'meta'));
    }

    public function rebuild(string $model)
    {
        $meta = $this->loadMeta($model);
        abort_unless($meta, 404);

        $output  = [];
        $errors  = [];

        // Clear all caches
        try {
            Artisan::call('optimize:clear');
            $output[] = 'Cache limpiado correctamente.';
        } catch (\Throwable $e) {
            $errors[] = 'Cache: ' . $e->getMessage();
        }

        // Run pending migrations
        try {
            Artisan::call('migrate', ['--force' => true]);
            $migOutput = Artisan::output();
            $output[] = 'Migración ejecutada: ' . (trim($migOutput) ?: 'Sin cambios pendientes.');
        } catch (\Throwable $e) {
            $errors[] = 'Migrate: ' . $e->getMessage();
        }

        return back()
            ->with('rebuild_model', $model)
            ->with('rebuild_output', $output)
            ->with('rebuild_errors', $errors);
    }

    public function destroyMeta(string $model)
    {
        $path = storage_path('app/' . self::META_DIR . '/' . $model . '.json');
        if (File::exists($path)) {
            File::delete($path);
        }
        return redirect()->route('crud-generator.index')
            ->with('info', "Registro de {$model} eliminado.");
    }

    public function destroyModule(string $model)
    {
        $meta      = $this->loadMeta($model);
        $tableName = $meta['table_name'] ?? Str::snake(Str::plural($model));
        $routeBase = Str::kebab(Str::plural($model));
        $ctrlClass = "{$model}Controller";
        $deleted   = [];

        // 1. Model
        $modelFile = app_path("Models/{$model}.php");
        if (File::exists($modelFile)) {
            File::delete($modelFile);
            $deleted[] = "app/Models/{$model}.php";
        }

        // 2. Controller
        $ctrlFile = app_path("Http/Controllers/{$ctrlClass}.php");
        if (File::exists($ctrlFile)) {
            File::delete($ctrlFile);
            $deleted[] = "app/Http/Controllers/{$ctrlClass}.php";
        }

        // 3. Views folder
        $viewDir = resource_path('views/' . $routeBase);
        if (File::isDirectory($viewDir)) {
            File::deleteDirectory($viewDir);
            $deleted[] = "resources/views/{$routeBase}/";
        }

        // 4. Migration(s)
        $migPattern = database_path('migrations');
        foreach (File::files($migPattern) as $file) {
            if (str_contains($file->getFilename(), "create_{$tableName}_table")) {
                File::delete($file->getPathname());
                $deleted[] = 'database/migrations/' . $file->getFilename();
            }
        }

        // 5. Remove route block + use import from web.php
        $webPath = base_path('routes/web.php');
        $webContent = File::get($webPath);

        // Remove data route line (DataTables)
        $webContent = preg_replace(
            "/\n\s*Route::get\(\s*'\/{$routeBase}\/data'[^\n]+\n/",
            "\n",
            $webContent
        );
        // Remove resource route line
        $webContent = preg_replace(
            "/\n\s*Route::resource\(\s*'{$routeBase}'[^\n]+\n/",
            "\n",
            $webContent
        );
        // Remove comment line  // ModelName
        $webContent = preg_replace(
            "/\n\s*\/\/ {$model}\n/",
            "\n",
            $webContent
        );
        // Remove use import
        $webContent = str_replace(
            "use App\\Http\\Controllers\\{$ctrlClass};\n",
            '',
            $webContent
        );
        // Collapse multiple blank lines inside the group
        $webContent = preg_replace("/(\n\s*){3,}/", "\n\n", $webContent);

        File::put($webPath, $webContent);
        $deleted[] = "routes/web.php (rutas de {$model} eliminadas)";

        // 6. Remove menu item from app.blade.php
        $layoutPath = resource_path('views/layouts/app.blade.php');
        $layoutContent = File::get($layoutPath);

        // Remove the <!-- Model --> ... </a> block
        $layoutContent = preg_replace(
            '/\n\s*<!-- ' . preg_quote($model, '/') . ' -->\s*\n\s*<a[^>]+route\(\'' . preg_quote($routeBase, '/') . '\.index\'\)[^>]*>.*?<\/a>/s',
            '',
            $layoutContent
        );

        // If the "Módulos" heading is now followed immediately by the end marker, remove it
        $startMarker = '{{-- @crud-menu-items-start --}}';
        $endMarker   = '{{-- @crud-menu-items-end --}}';
        preg_match('/' . preg_quote($startMarker, '/') . '(.*?)' . preg_quote($endMarker, '/') . '/s', $layoutContent, $m);
        if (isset($m[1]) && !str_contains($m[1], '<a ')) {
            // Strip the Módulos heading too
            $layoutContent = preg_replace(
                '/' . preg_quote($startMarker, '/') . '.*?' . preg_quote($endMarker, '/') . '/s',
                $startMarker . "\n            " . $endMarker,
                $layoutContent
            );
        }

        File::put($layoutPath, $layoutContent);
        $deleted[] = "layouts/app.blade.php (ítem de menú eliminado)";

        // 7. Delete meta JSON
        $metaPath = storage_path('app/' . self::META_DIR . '/' . $model . '.json');
        if (File::exists($metaPath)) {
            File::delete($metaPath);
            $deleted[] = "storage/app/crud-generator/{$model}.json";
        }

        return redirect()->route('crud-generator.index')
            ->with('module_deleted', $model)
            ->with('module_deleted_files', $deleted);
    }

    // ─── Generators ──────────────────────────────────────────────────────────

    private function makeModel(string $name, array $fields, bool $timestamps, bool $softDeletes): string
    {
        $fillable = collect($fields)
            ->pluck('name')
            ->map(fn($n) => "        '{$n}'")
            ->implode(",\n");

        $sdImport = $softDeletes ? "\nuse Illuminate\\Database\\Eloquent\\SoftDeletes;" : '';
        $sdTrait  = $softDeletes ? "    use SoftDeletes;\n\n" : '';
        $noTs     = $timestamps  ? '' : "\n\n    public \$timestamps = false;";

        $out  = "<?php\n\nnamespace App\\Models;\n\n";
        $out .= "use Illuminate\\Database\\Eloquent\\Model;{$sdImport}\n\n";
        $out .= "class {$name} extends Model\n{\n";
        $out .= $sdTrait;
        $out .= "    protected \$fillable = [\n{$fillable},\n    ];{$noTs}\n}\n";

        return $out;
    }

    private function makeMigration(string $table, array $fields, bool $timestamps, bool $softDeletes): string
    {
        $columns = collect($fields)->map(function ($field) {
            $type     = $field['type'];
            $name     = $field['name'];
            $nullable = !empty($field['nullable']);
            $default  = $field['default'] ?? '';
            $length   = trim($field['length'] ?? '');
            $enumVals = trim($field['enum_values'] ?? '');

            // image/file → stored as string (path)
            if (in_array($type, ['image', 'file'])) {
                $col = "\$table->string('{$name}')->nullable()";
                $col .= ';';
                return "            {$col}";
            }

            // Normalize datetime → dateTime (Blueprint method name)
            $method = $type === 'datetime' ? 'dateTime' : $type;

            if ($type === 'enum' && $enumVals) {
                $vals = array_map(fn($v) => "'" . addslashes(trim($v)) . "'", explode(',', $enumVals));
                $col  = "\$table->enum('{$name}', [" . implode(', ', $vals) . "])";
            } elseif (in_array($type, ['decimal', 'float']) && $length) {
                $parts = explode(',', $length);
                $p = (int) trim($parts[0]);
                $s = isset($parts[1]) ? (int) trim($parts[1]) : 2;
                $col = "\$table->{$method}('{$name}', {$p}, {$s})";
            } elseif ($type === 'string' && $length) {
                $col = "\$table->string('{$name}', {$length})";
            } else {
                $col = "\$table->{$method}('{$name}')";
            }

            if ($nullable)       $col .= '->nullable()';
            if ($default !== '') $col .= "->default('" . addslashes($default) . "')";
            $col .= ';';

            return "            {$col}";
        })->implode("\n");

        $sd = $softDeletes ? "\n            \$table->softDeletes();" : '';
        $ts = $timestamps  ? "\n            \$table->timestamps();"  : '';

        $out  = "<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\n";
        $out .= "use Illuminate\\Database\\Schema\\Blueprint;\n";
        $out .= "use Illuminate\\Support\\Facades\\Schema;\n\n";
        $out .= "return new class extends Migration\n{\n";
        $out .= "    public function up(): void\n    {\n";
        $out .= "        Schema::create('{$table}', function (Blueprint \$table) {\n";
        $out .= "            \$table->id();\n";
        $out .= $columns;
        $out .= $sd . $ts;
        $out .= "\n        });\n    }\n\n";
        $out .= "    public function down(): void\n    {\n";
        $out .= "        Schema::dropIfExists('{$table}');\n    }\n};\n";

        return $out;
    }

    private function makeController(string $model, string $table, array $fields, bool $dt): string
    {
        $modelVar       = Str::camel($model);
        $modelVarPlural = Str::plural(Str::camel($model));
        $routePrefix    = Str::kebab(Str::plural($model));
        $viewPrefix     = $routePrefix;
        $titleSingular  = Str::headline($model);

        $fileFields = collect($fields)->filter(fn($f) => in_array($f['type'], ['image', 'file']))->values();
        $hasFileFields = $fileFields->isNotEmpty();

        // Validation rules
        $rules = collect($fields)->map(function ($f) {
            $nullable = !empty($f['nullable']);
            $type     = $f['type'];
            $enumVals = trim($f['enum_values'] ?? '');
            $accept   = trim($f['accept'] ?? '');

            // File/image fields are always nullable on update (re-upload is optional)
            if (in_array($type, ['image', 'file'])) {
                $mimes = $accept ?: ($type === 'image' ? 'jpg,jpeg,png,gif,webp' : 'pdf,xlsx,xls,docx,doc,zip');
                $maxKb = $type === 'image' ? '2048' : '10240';
                $n = $f['name'];
                return "            '{$n}' => ['nullable', 'file', 'mimes:{$mimes}', 'max:{$maxKb}'],";
            }

            $req  = $nullable ? "'nullable'" : "'required'";
            $extra = match ($type) {
                'string'                 => ", 'string', 'max:255'",
                'text'                   => ", 'string'",
                'integer', 'bigInteger'  => ", 'integer'",
                'boolean'                => ", 'boolean'",
                'date', 'datetime'       => ", 'date'",
                'decimal', 'float'       => ", 'numeric'",
                'enum' => $enumVals ? ", 'in:" . str_replace(' ', '', $enumVals) . "'" : '',
                default => '',
            };
            $n = $f['name'];
            return "            '{$n}' => [{$req}{$extra}],";
        })->implode("\n");

        // Non-file fields for $request->only()
        $plainFields   = collect($fields)->filter(fn($f) => !in_array($f['type'], ['image', 'file']));
        $onlyFields    = $plainFields->pluck('name')->map(fn($n) => "'{$n}'")->implode(', ');

        $dtImport      = $dt ? "\nuse Yajra\\DataTables\\Facades\\DataTables;" : '';
        $storageImport = $hasFileFields ? "\nuse Illuminate\\Support\\Facades\\Storage;" : '';

        $out  = "<?php\n\nnamespace App\\Http\\Controllers;\n\n";
        $out .= "use App\\Models\\{$model};\n";
        $out .= "use Illuminate\\Http\\Request;{$dtImport}{$storageImport}\n\n";
        $out .= "class {$model}Controller extends Controller\n{\n";

        // index
        if ($dt) {
            $out .= "    public function index()\n    {\n";
            $out .= "        return view('{$viewPrefix}.index');\n    }\n";
            // data()
            $out .= "\n    public function data()\n    {\n";
            $out .= "        \$query = {$model}::query();\n\n";
            $out .= "        \$editSvg   = '<svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z\"/></svg>';\n";
            $out .= "        \$trashSvg  = '<svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16\"/></svg>';\n\n";
            $out .= "        return DataTables::of(\$query)\n";
            $out .= "            ->addColumn('actions', function (\${$modelVar}) use (\$editSvg, \$trashSvg) {\n";
            $out .= "                \$edit = route('{$routePrefix}.edit', \${$modelVar});\n";
            $out .= "                \$del  = route('{$routePrefix}.destroy', \${$modelVar});\n";
            $out .= "                \$csrf = csrf_field();\n";
            $out .= "                \$html  = '<div class=\"inline-flex items-center justify-end gap-x-1\">';\n";
            $out .= "                \$html .= '<a href=\"' . \$edit . '\" class=\"p-1.5 text-slate-400 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition-all\" title=\"Editar\">' . \$editSvg . '</a>';\n";
            $out .= "                \$html .= '<form action=\"' . \$del . '\" method=\"POST\" style=\"display:inline\" onsubmit=\"return confirm(\\'¿Eliminar este registro?\\')\">'\n";
            $out .= "                       . \$csrf . method_field('DELETE')\n";
            $out .= "                       . '<button type=\"submit\" class=\"p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all\" title=\"Eliminar\">' . \$trashSvg . '</button></form>';\n";
            $out .= "                \$html .= '</div>';\n";
            $out .= "                return \$html;\n";
            $out .= "            })\n";

            // Add extra columns for image/file fields
            $rawCols = ["'actions'"];
            foreach ($fileFields as $ff) {
                $fn = $ff['name'];
                $rawCols[] = "'{$fn}'";
                if ($ff['type'] === 'image') {
                    $out .= "            ->addColumn('{$fn}', function (\${$modelVar}) {\n";
                    $out .= "                if (!\${$modelVar}->{$fn}) return '<span class=\"text-xs text-slate-400\">—</span>';\n";
                    $out .= "                return '<img src=\"' . asset('storage/' . \${$modelVar}->{$fn}) . '\" class=\"h-10 w-10 object-cover rounded-lg border border-slate-200\">';\n";
                    $out .= "            })\n";
                } else {
                    $out .= "            ->addColumn('{$fn}', function (\${$modelVar}) {\n";
                    $out .= "                if (!\${$modelVar}->{$fn}) return '<span class=\"text-xs text-slate-400\">—</span>';\n";
                    $out .= "                return '<a href=\"' . asset('storage/' . \${$modelVar}->{$fn}) . '\" target=\"_blank\" class=\"text-xs text-brand-600 hover:underline\">Ver</a>';\n";
                    $out .= "            })\n";
                }
            }
            $rawColsStr = implode(', ', $rawCols);
            $out .= "            ->rawColumns([{$rawColsStr}])\n";
            $out .= "            ->make(true);\n    }\n";
        } else {
            $out .= "    public function index()\n    {\n";
            $out .= "        \${$modelVarPlural} = {$model}::latest()->paginate(15);\n";
            $out .= "        return view('{$viewPrefix}.index', compact('{$modelVarPlural}'));\n    }\n";
        }

        // create
        $out .= "\n    public function create()\n    {\n";
        $out .= "        return view('{$viewPrefix}.create');\n    }\n";

        // store
        $out .= "\n    public function store(Request \$request)\n    {\n";
        $out .= "        \$request->validate([\n{$rules}\n        ]);\n\n";

        if ($hasFileFields) {
            // Build manual data array for store
            $storeLines = '';
            if ($onlyFields) {
                $storeLines .= "        \$data = \$request->only([{$onlyFields}]);\n";
            } else {
                $storeLines .= "        \$data = [];\n";
            }
            foreach ($fileFields as $ff) {
                $fn   = $ff['name'];
                $fdir = "uploads/{$routePrefix}";
                $storeLines .= "        if (\$request->hasFile('{$fn}')) {\n";
                $storeLines .= "            \$data['{$fn}'] = \$request->file('{$fn}')->store('{$fdir}', 'public');\n";
                $storeLines .= "        }\n";
            }
            $out .= $storeLines;
            $out .= "        {$model}::create(\$data);\n\n";
        } else {
            $out .= "        {$model}::create(\$request->only([{$onlyFields}]));\n\n";
        }

        $out .= "        return redirect()->route('{$routePrefix}.index')\n";
        $out .= "            ->with('success', '{$titleSingular} creado correctamente.');\n    }\n";

        // edit
        $out .= "\n    public function edit({$model} \${$modelVar})\n    {\n";
        $out .= "        return view('{$viewPrefix}.edit', compact('{$modelVar}'));\n    }\n";

        // update
        $out .= "\n    public function update(Request \$request, {$model} \${$modelVar})\n    {\n";
        $out .= "        \$request->validate([\n{$rules}\n        ]);\n\n";

        if ($hasFileFields) {
            $updateLines = '';
            if ($onlyFields) {
                $updateLines .= "        \$data = \$request->only([{$onlyFields}]);\n";
            } else {
                $updateLines .= "        \$data = [];\n";
            }
            foreach ($fileFields as $ff) {
                $fn   = $ff['name'];
                $fdir = "uploads/{$routePrefix}";
                $updateLines .= "        if (\$request->hasFile('{$fn}')) {\n";
                $updateLines .= "            if (\${$modelVar}->{$fn}) Storage::disk('public')->delete(\${$modelVar}->{$fn});\n";
                $updateLines .= "            \$data['{$fn}'] = \$request->file('{$fn}')->store('{$fdir}', 'public');\n";
                $updateLines .= "        }\n";
            }
            $out .= $updateLines;
            $out .= "        \${$modelVar}->update(\$data);\n\n";
        } else {
            $out .= "        \${$modelVar}->update(\$request->only([{$onlyFields}]));\n\n";
        }

        $out .= "        return redirect()->route('{$routePrefix}.index')\n";
        $out .= "            ->with('success', '{$titleSingular} actualizado correctamente.');\n    }\n";

        // destroy
        $out .= "\n    public function destroy({$model} \${$modelVar})\n    {\n";
        $out .= "        \${$modelVar}->delete();\n\n";
        $out .= "        return redirect()->route('{$routePrefix}.index')\n";
        $out .= "            ->with('success', '{$titleSingular} eliminado correctamente.');\n    }\n";

        $out .= "}\n";

        return $out;
    }

    private function makeIndexView(string $model, array $fields, bool $dt): string
    {
        $modelVar       = Str::camel($model);
        $modelVarPlural = Str::plural(Str::camel($model));
        $routePrefix    = Str::kebab(Str::plural($model));
        $titlePlural    = Str::headline(Str::plural($model));

        if ($dt) {
            return $this->makeIndexViewDT($model, $modelVar, $routePrefix, $titlePlural, $fields);
        }

        $ths = collect($fields)->map(
            fn($f) => "                            <th class=\"px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500\">"
                    . Str::headline($f['name']) . "</th>"
        )->implode("\n");

        $tds = collect($fields)->map(function ($f) use ($modelVar) {
            $n = $f['name'];
            if ($f['type'] === 'boolean') {
                return "                            <td class=\"px-5 py-3.5\">\n"
                    . "                                <span class=\"inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ \${$modelVar}->{$n} ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}\">\n"
                    . "                                    {{ \${$modelVar}->{$n} ? 'Sí' : 'No' }}\n"
                    . "                                </span>\n"
                    . "                            </td>";
            }
            if ($f['type'] === 'image') {
                return "                            <td class=\"px-5 py-3.5\">\n"
                    . "                                @if(\${$modelVar}->{$n})\n"
                    . "                                <img src=\"{{ asset('storage/' . \${$modelVar}->{$n}) }}\" alt=\"{$n}\" class=\"h-10 w-10 object-cover rounded-lg border border-slate-200 dark:border-slate-700\">\n"
                    . "                                @else\n"
                    . "                                <span class=\"text-xs text-slate-400\">—</span>\n"
                    . "                                @endif\n"
                    . "                            </td>";
            }
            if ($f['type'] === 'file') {
                return "                            <td class=\"px-5 py-3.5\">\n"
                    . "                                @if(\${$modelVar}->{$n})\n"
                    . "                                <a href=\"{{ asset('storage/' . \${$modelVar}->{$n}) }}\" target=\"_blank\" class=\"inline-flex items-center gap-x-1 text-xs text-brand-600 hover:underline\">\n"
                    . "                                    <svg class=\"w-3.5 h-3.5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13\"/></svg>\n"
                    . "                                    Ver\n"
                    . "                                </a>\n"
                    . "                                @else\n"
                    . "                                <span class=\"text-xs text-slate-400\">—</span>\n"
                    . "                                @endif\n"
                    . "                            </td>";
            }
            $val = "{{ \${$modelVar}->{$n} }}";
            return "                            <td class=\"px-5 py-3.5 text-sm text-slate-700 dark:text-slate-300 max-w-[200px] truncate\">{$val}</td>";
        })->implode("\n");

        $colCount = count($fields) + 2;

        $out  = "<x-app-layout>\n    <x-slot name=\"header\">{$titlePlural}</x-slot>\n\n";
        $out .= "    <div class=\"space-y-5\">\n\n";
        $out .= "        @if(session('success'))\n";
        $out .= "        <div class=\"flex items-center gap-x-2 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl px-4 py-3\">\n";
        $out .= "            <svg class=\"w-4 h-4 text-emerald-500 flex-shrink-0\" fill=\"currentColor\" viewBox=\"0 0 20 20\"><path fill-rule=\"evenodd\" d=\"M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z\" clip-rule=\"evenodd\"/></svg>\n";
        $out .= "            <p class=\"text-sm text-emerald-700 dark:text-emerald-300\">{{ session('success') }}</p>\n";
        $out .= "        </div>\n        @endif\n\n";
        $out .= "        <div class=\"flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3\">\n";
        $out .= "            <div>\n";
        $out .= "                <h1 class=\"text-xl font-bold text-slate-800 dark:text-white\">{$titlePlural}</h1>\n";
        $out .= "                <p class=\"text-sm text-slate-500 dark:text-slate-400 mt-0.5\">Gestiona los registros de {$titlePlural}.</p>\n";
        $out .= "            </div>\n";
        $out .= "            <a href=\"{{ route('{$routePrefix}.create') }}\"\n";
        $out .= "               class=\"inline-flex items-center gap-x-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm\">\n";
        $out .= "                <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 4v16m8-8H4\"/></svg>\n";
        $out .= "                Nuevo {$model}\n            </a>\n        </div>\n\n";
        $out .= "        <div class=\"bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden\">\n";
        $out .= "            <div class=\"overflow-x-auto\">\n";
        $out .= "                <table class=\"min-w-full\">\n";
        $out .= "                    <thead>\n";
        $out .= "                        <tr class=\"border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50\">\n";
        $out .= "                            <th class=\"px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400\">#</th>\n";
        $out .= $ths . "\n";
        $out .= "                            <th class=\"px-5 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400\">Acciones</th>\n";
        $out .= "                        </tr>\n                    </thead>\n";
        $out .= "                    <tbody class=\"divide-y divide-slate-100 dark:divide-slate-800\">\n";
        $out .= "                        @forelse(${$modelVarPlural} as ${$modelVar})\n";
        $out .= "                        <tr class=\"hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors group\">\n";
        $out .= "                            <td class=\"px-5 py-3.5 text-sm font-mono text-slate-400\">{{ ${$modelVar}->id }}</td>\n";
        $out .= $tds . "\n";
        $out .= "                            <td class=\"px-5 py-3.5 text-right\">\n";
        $out .= "                                <div class=\"inline-flex items-center gap-x-1 opacity-0 group-hover:opacity-100 transition-opacity\">\n";
        $out .= "                                    <a href=\"{{ route('{$routePrefix}.edit', ${$modelVar}) }}\"\n";
        $out .= "                                       class=\"p-1.5 text-slate-400 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 rounded-lg transition-all\" title=\"Editar\">\n";
        $out .= "                                        <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z\"/></svg>\n";
        $out .= "                                    </a>\n";
        $out .= "                                    <form action=\"{{ route('{$routePrefix}.destroy', ${$modelVar}) }}\" method=\"POST\"\n";
        $out .= "                                          onsubmit=\"return confirm('\u00bfEliminar este registro?')\">\n";
        $out .= "                                        @csrf @method('DELETE')\n";
        $out .= "                                        <button type=\"submit\" class=\"p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all\" title=\"Eliminar\">\n";
        $out .= "                                            <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16\"/></svg>\n";
        $out .= "                                        </button>\n                                    </form>\n";
        $out .= "                                </div>\n                            </td>\n";
        $out .= "                        </tr>\n";
        $out .= "                        @empty\n";
        $out .= "                        <tr><td colspan=\"{$colCount}\" class=\"px-5 py-16 text-center\">\n";
        $out .= "                            <div class=\"flex flex-col items-center gap-y-2\">\n";
        $out .= "                                <svg class=\"w-10 h-10 text-slate-200 dark:text-slate-700\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"1.5\" d=\"M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2\"/></svg>\n";
        $out .= "                                <p class=\"text-sm text-slate-400\">No hay registros a\u00fan.</p>\n";
        $out .= "                                <a href=\"{{ route('{$routePrefix}.create') }}\" class=\"text-xs text-brand-600 hover:underline font-medium\">Crear el primero</a>\n";
        $out .= "                            </div>\n        </td></tr>\n";
        $out .= "                        @endforelse\n                    </tbody>\n                </table>\n            </div>\n";
        $out .= "            @if(${$modelVarPlural}->hasPages())\n";
        $out .= "            <div class=\"px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30\">\n";
        $out .= "                {{ ${$modelVarPlural}->links() }}\n";
        $out .= "            </div>\n            @endif\n";
        $out .= "        </div>\n    </div>\n</x-app-layout>\n";

        return $out;
    }

    private function makeIndexViewDT(string $model, string $modelVar, string $routePrefix, string $titlePlural, array $fields): string
    {
        $ths = collect($fields)->map(
            fn($f) => "                        <th class=\"px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400\">"
                    . Str::headline($f['name']) . "</th>"
        )->implode("\n");

        $dtCols = collect($fields)->map(function ($f) {
            $base = "{ data: '{$f['name']}', className: 'px-4 py-3 text-sm text-slate-700'";
            if (in_array($f['type'], ['image', 'file'])) {
                return "                    " . $base . ", orderable: false, searchable: false }";
            }
            return "                    " . $base . " }";
        })->implode(",\n");

        $dataRoute   = "{{ route('{$routePrefix}.data') }}";
        $createRoute = "{{ route('{$routePrefix}.create') }}";

        $out  = "<x-app-layout>\n    <x-slot name=\"header\">{$titlePlural}</x-slot>\n\n";
        $out .= "    <div class=\"space-y-5\">\n\n";
        $out .= "        @if(session('success'))\n";
        $out .= "        <div class=\"flex items-center gap-x-2 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl px-4 py-3\">\n";
        $out .= "            <svg class=\"w-4 h-4 text-emerald-500 flex-shrink-0\" fill=\"currentColor\" viewBox=\"0 0 20 20\"><path fill-rule=\"evenodd\" d=\"M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z\" clip-rule=\"evenodd\"/></svg>\n";
        $out .= "            <p class=\"text-sm text-emerald-700 dark:text-emerald-300\">{{ session('success') }}</p>\n";
        $out .= "        </div>\n        @endif\n\n";
        $out .= "        <div class=\"flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3\">\n";
        $out .= "            <div>\n";
        $out .= "                <h1 class=\"text-xl font-bold text-slate-800 dark:text-white\">{$titlePlural}</h1>\n";
        $out .= "                <p class=\"text-sm text-slate-500 dark:text-slate-400 mt-0.5\">Gestiona los registros de {$titlePlural}.</p>\n";
        $out .= "            </div>\n";
        $out .= "            <a href=\"{$createRoute}\"\n";
        $out .= "               class=\"inline-flex items-center gap-x-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm\">\n";
        $out .= "                <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 4v16m8-8H4\"/></svg>\n";
        $out .= "                Nuevo {$model}\n            </a>\n        </div>\n\n";
        $out .= "        <div class=\"bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden\">\n";
        $out .= "            <div class=\"h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500\"></div>\n";
        $out .= "            <div class=\"p-5\">\n";
        $out .= "            <table id=\"dt-{$modelVar}\" class=\"w-full\" style=\"width:100%\">\n";
        $out .= "                <thead>\n";
        $out .= "                    <tr class=\"border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50\">\n";
        $out .= "                        <th class=\"px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400\">#</th>\n";
        $out .= $ths . "\n";
        $out .= "                        <th class=\"px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400\">Acciones</th>\n";
        $out .= "                    </tr>\n                </thead>\n            </table>\n            </div>\n        </div>\n    </div>\n\n";
        $out .= "@push('scripts')\n<script>\n$(function () {\n";
        $out .= "    $('#dt-{$modelVar}').DataTable({\n";
        $out .= "        processing: true,\n        serverSide: true,\n";
        $out .= "        ajax: '{$dataRoute}',\n";
        $out .= "        language: { url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json' },\n";
        $out .= "        dom: '<\"flex flex-wrap items-center justify-between gap-2 mb-3\"lB>frtip',\n";
        $out .= "        buttons: [\n";
        $out .= "            {\n";
        $out .= "                extend: 'excelHtml5', text: 'Excel', className: 'dt-btn-excel',\n";
        $out .= "                exportOptions: { columns: ':not(:last-child)' }\n";
        $out .= "            },\n";
        $out .= "            {\n";
        $out .= "                extend: 'pdfHtml5', text: 'PDF', className: 'dt-btn-pdf',\n";
        $out .= "                orientation: 'landscape', pageSize: 'A4',\n";
        $out .= "                exportOptions: { columns: ':not(:last-child)' },\n";
        $out .= "                customize: function(doc) {\n";
        $out .= "                    doc.pageMargins = [30, 45, 30, 50];\n";
        $out .= "                    if (doc.content[0]) {\n";
        $out .= "                        doc.content[0].fontSize = 16; doc.content[0].bold = true;\n";
        $out .= "                        doc.content[0].color = '#1e293b'; doc.content[0].margin = [0,0,0,14];\n";
        $out .= "                    }\n";
        $out .= "                    var tbl = doc.content[doc.content.length - 1];\n";
        $out .= "                    var body = tbl.table.body;\n";
        $out .= "                    body[0].forEach(function(c) {\n";
        $out .= "                        c.fillColor = '#4f46e5'; c.color = '#ffffff';\n";
        $out .= "                        c.bold = true; c.fontSize = 8; c.margin = [5,7,5,7];\n";
        $out .= "                    });\n";
        $out .= "                    for (var i = 1; i < body.length; i++) {\n";
        $out .= "                        body[i].forEach(function(c) {\n";
        $out .= "                            c.fontSize = 8; c.color = '#334155';\n";
        $out .= "                            c.fillColor = i % 2 === 0 ? '#f1f5f9' : '#ffffff';\n";
        $out .= "                            c.margin = [5,5,5,5];\n";
        $out .= "                        });\n";
        $out .= "                    }\n";
        $out .= "                    tbl.layout = {\n";
        $out .= "                        hLineWidth: function(i,n) { return (i===0||i===1||i===n.table.body.length)?0:0.5; },\n";
        $out .= "                        vLineWidth: function() { return 0; },\n";
        $out .= "                        hLineColor: function() { return '#e2e8f0'; }\n";
        $out .= "                    };\n";
        $out .= "                    tbl.table.widths = Array(body[0].length).fill('*');\n";
        $out .= "                    doc.footer = function(p,n) {\n";
        $out .= "                        return { columns: [\n";
        $out .= "                            { text: new Date().toLocaleDateString('es-ES',{day:'2-digit',month:'long',year:'numeric'}), fontSize:7, color:'#94a3b8', margin:[30,0,0,0] },\n";
        $out .= "                            { text: 'P\u00e1gina '+p+' de '+n, fontSize:7, color:'#94a3b8', alignment:'right', margin:[0,0,30,0] }\n";
        $out .= "                        ], margin:[0,10,0,0] };\n";
        $out .= "                    };\n";
        $out .= "                }\n";
        $out .= "            },\n";
        $out .= "            {\n";
        $out .= "                extend: 'print', text: 'Imprimir', className: 'dt-btn-print',\n";
        $out .= "                exportOptions: { columns: ':not(:last-child)' }\n";
        $out .= "            },\n";
        $out .= "        ],\n";
        $out .= "        columns: [\n";
        $out .= "            { data: 'id', className: 'px-4 py-3 text-sm font-mono text-slate-400' },\n";
        $out .= $dtCols . ",\n";
        $out .= "            { data: 'actions', orderable: false, searchable: false, className: 'px-4 py-3 text-right' }\n";
        $out .= "        ]\n    });\n});\n</script>\n@endpush\n";
        $out .= "</x-app-layout>\n";

        return $out;
    }
    private function makeCreateView(string $model, array $fields): string
    {
        $routePrefix   = Str::kebab(Str::plural($model));
        $titleSingular = Str::headline($model);

        $hasFileFields = collect($fields)->contains(fn($f) => in_array($f['type'], ['image', 'file']));
        $enctype       = $hasFileFields ? ' enctype="multipart/form-data"' : '';

        $formFields = collect($fields)->map(fn($f) => $this->makeFormField($f, null))->implode("\n\n");

        $out  = "<x-app-layout>\n";
        $out .= "    <x-slot name=\"header\">Nuevo {$titleSingular}</x-slot>\n\n";
        $out .= "    <div class=\"max-w-2xl mx-auto space-y-4\">\n\n";
        $out .= "        {{-- Breadcrumb --}}\n";
        $out .= "        <nav class=\"flex items-center gap-x-1.5 text-xs text-slate-400\">\n";
        $out .= "            <a href=\"{{ route('{$routePrefix}.index') }}\" class=\"hover:text-brand-600 transition-colors\">" . Str::headline(Str::plural($model)) . "</a>\n";
        $out .= "            <svg class=\"w-3 h-3\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 5l7 7-7 7\"/></svg>\n";
        $out .= "            <span class=\"text-slate-600 dark:text-slate-300 font-medium\">Nuevo</span>\n";
        $out .= "        </nav>\n\n";
        $out .= "        <div class=\"bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm\">\n";
        $out .= "            <div class=\"px-6 py-4 border-b border-slate-100 dark:border-slate-800\">\n";
        $out .= "                <h1 class=\"text-base font-semibold text-slate-800 dark:text-white\">Crear {$titleSingular}</h1>\n";
        $out .= "                <p class=\"text-xs text-slate-400 mt-0.5\">Completa el formulario para agregar un nuevo registro.</p>\n";
        $out .= "            </div>\n";
        $out .= "            <form action=\"{{ route('{$routePrefix}.store') }}\" method=\"POST\"{$enctype} class=\"p-6 space-y-5\">\n";
        $out .= "                @csrf\n\n";
        $out .= $formFields . "\n\n";
        $out .= "                <div class=\"flex items-center justify-end gap-x-3 pt-3 border-t border-slate-100 dark:border-slate-800\">\n";
        $out .= "                    <a href=\"{{ route('{$routePrefix}.index') }}\"\n";
        $out .= "                       class=\"px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors\">Cancelar</a>\n";
        $out .= "                    <button type=\"submit\"\n";
        $out .= "                            class=\"inline-flex items-center gap-x-2 px-5 py-2.5 text-sm font-semibold bg-brand-600 hover:bg-brand-700 text-white rounded-xl transition-all shadow-sm\">\n";
        $out .= "                        <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M5 13l4 4L19 7\"/></svg>\n";
        $out .= "                        Guardar\n                    </button>\n                </div>\n";
        $out .= "            </form>\n        </div>\n    </div>\n</x-app-layout>\n";

        return $out;
    }

    private function makeEditView(string $model, array $fields): string
    {
        $routePrefix   = Str::kebab(Str::plural($model));
        $modelVar      = Str::camel($model);
        $titleSingular = Str::headline($model);

        $hasFileFields = collect($fields)->contains(fn($f) => in_array($f['type'], ['image', 'file']));
        $enctype       = $hasFileFields ? ' enctype="multipart/form-data"' : '';

        $formFields = collect($fields)->map(fn($f) => $this->makeFormField($f, $modelVar))->implode("\n\n");

        $out  = "<x-app-layout>\n";
        $out .= "    <x-slot name=\"header\">Editar {$titleSingular}</x-slot>\n\n";
        $out .= "    <div class=\"max-w-2xl mx-auto space-y-4\">\n\n";
        $out .= "        {{-- Breadcrumb --}}\n";
        $out .= "        <nav class=\"flex items-center gap-x-1.5 text-xs text-slate-400\">\n";
        $out .= "            <a href=\"{{ route('{$routePrefix}.index') }}\" class=\"hover:text-brand-600 transition-colors\">" . Str::headline(Str::plural($model)) . "</a>\n";
        $out .= "            <svg class=\"w-3 h-3\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 5l7 7-7 7\"/></svg>\n";
        $out .= "            <span class=\"text-slate-600 dark:text-slate-300 font-medium\">Editar #{{ \${$modelVar}->id }}</span>\n";
        $out .= "        </nav>\n\n";
        $out .= "        <div class=\"bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm\">\n";
        $out .= "            <div class=\"px-6 py-4 border-b border-slate-100 dark:border-slate-800\">\n";
        $out .= "                <h1 class=\"text-base font-semibold text-slate-800 dark:text-white\">Editar {$titleSingular}</h1>\n";
        $out .= "                <p class=\"text-xs text-slate-400 mt-0.5\">Modifica los datos del registro.</p>\n";
        $out .= "            </div>\n";
        $out .= "            <form action=\"{{ route('{$routePrefix}.update', \${$modelVar}) }}\" method=\"POST\"{$enctype} class=\"p-6 space-y-5\">\n";
        $out .= "                @csrf\n                @method('PUT')\n\n";
        $out .= $formFields . "\n\n";
        $out .= "                <div class=\"flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-800\">\n";
        $out .= "                    <form action=\"{{ route('{$routePrefix}.destroy', \${$modelVar}) }}\" method=\"POST\"\n";
        $out .= "                          onsubmit=\"return confirm('¿Eliminar este registro? Esta acción no se puede deshacer.')\">\n";
        $out .= "                        @csrf @method('DELETE')\n";
        $out .= "                        <button type=\"submit\" class=\"inline-flex items-center gap-x-1.5 px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all\">\n";
        $out .= "                            <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16\"/></svg>\n";
        $out .= "                            Eliminar\n                        </button>\n                    </form>\n";
        $out .= "                    <div class=\"flex items-center gap-x-3\">\n";
        $out .= "                        <a href=\"{{ route('{$routePrefix}.index') }}\"\n";
        $out .= "                           class=\"px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors\">Cancelar</a>\n";
        $out .= "                        <button type=\"submit\"\n";
        $out .= "                                class=\"inline-flex items-center gap-x-2 px-5 py-2.5 text-sm font-semibold bg-brand-600 hover:bg-brand-700 text-white rounded-xl transition-all shadow-sm\">\n";
        $out .= "                            <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M5 13l4 4L19 7\"/></svg>\n";
        $out .= "                            Actualizar\n                        </button>\n                    </div>\n";
        $out .= "                </div>\n            </form>\n        </div>\n    </div>\n</x-app-layout>\n";

        return $out;
    }

    private function makeFormField(array $field, ?string $modelVar): string
    {
        $name     = $field['name'];
        $type     = $field['type'];
        $label    = Str::headline($name);
        $nullable = !empty($field['nullable']);
        $required = $nullable ? '' : ' required';
        $enumVals = trim($field['enum_values'] ?? '');

        $oldSuffix = $modelVar ? ", \${$modelVar}->{$name}" : '';
        $oldVal    = "{{ old('{$name}'{$oldSuffix}) }}";
        $inputClass = "py-3 px-4 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400 @error('{$name}') border-red-500 @enderror";

        $out = "                <div>\n";
        $out .= "                    <label for=\"{$name}\" class=\"block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5\">{$label}</label>\n";

        if ($type === 'text') {
            $out .= "                    <textarea id=\"{$name}\" name=\"{$name}\" rows=\"4\"{$required} class=\"{$inputClass}\">{$oldVal}</textarea>\n";
        } elseif ($type === 'boolean') {
            $checked = $modelVar
                ? "{{ old('{$name}', \${$modelVar}->{$name}) ? 'checked' : '' }}"
                : "{{ old('{$name}') ? 'checked' : '' }}";
            $out .= "                    <label class=\"flex items-center gap-x-2 cursor-pointer\">\n";
            $out .= "                        <input type=\"hidden\" name=\"{$name}\" value=\"0\">\n";
            $out .= "                        <input id=\"{$name}\" type=\"checkbox\" name=\"{$name}\" value=\"1\" {$checked}\n";
            $out .= "                               class=\"shrink-0 mt-0.5 border-gray-200 rounded text-brand-600 focus:ring-brand-500\">\n";
            $out .= "                        <span class=\"text-sm text-slate-600 dark:text-slate-400\">{$label}</span>\n";
            $out .= "                    </label>\n";
        } elseif ($type === 'enum' && $enumVals) {
            $options = array_map('trim', explode(',', $enumVals));
            $opt  = "\n                        <option value=\"\" disabled {{ old('{$name}'{$oldSuffix}) ? '' : 'selected' }}>Selecciona...</option>\n";
            foreach ($options as $o) {
                $opt .= "                        <option value=\"{$o}\" {{ old('{$name}'{$oldSuffix}) === '{$o}' ? 'selected' : '' }}>{$o}</option>\n";
            }
            $out .= "                    <select id=\"{$name}\" name=\"{$name}\"{$required} class=\"{$inputClass}\">{$opt}                    </select>\n";
        } elseif ($type === 'date') {
            $out .= "                    <input id=\"{$name}\" type=\"date\" name=\"{$name}\" value=\"{$oldVal}\"{$required} class=\"{$inputClass}\">\n";
        } elseif ($type === 'datetime') {
            $out .= "                    <input id=\"{$name}\" type=\"datetime-local\" name=\"{$name}\" value=\"{$oldVal}\"{$required} class=\"{$inputClass}\">\n";
        } elseif (in_array($type, ['integer', 'bigInteger'])) {
            $out .= "                    <input id=\"{$name}\" type=\"number\" name=\"{$name}\" value=\"{$oldVal}\"{$required} class=\"{$inputClass}\">\n";
        } elseif (in_array($type, ['decimal', 'float'])) {
            $out .= "                    <input id=\"{$name}\" type=\"number\" step=\"0.01\" name=\"{$name}\" value=\"{$oldVal}\"{$required} class=\"{$inputClass}\">\n";
        } elseif ($type === 'image') {
            $accept    = trim($field['accept'] ?? '');
            $mimes     = $accept ?: 'jpg,jpeg,png,gif,webp';
            $acceptAttr = implode(',', array_map(fn($e) => '.' . trim($e), explode(',', $mimes)));
            $out .= "                    <input id=\"{$name}\" type=\"file\" name=\"{$name}\" accept=\"{$acceptAttr}\"{$required} class=\"{$inputClass}\">\n";
            if ($modelVar) {
                $out .= "                    @if(\${$modelVar}->{$name})\n";
                $out .= "                    <div class=\"mt-2 flex items-center gap-x-3\">\n";
                $out .= "                    <img src=\"{{ asset('storage/' . \${$modelVar}->{$name}) }}\" alt=\"{$label}\" class=\"h-16 w-16 object-cover rounded-lg border border-slate-200 dark:border-slate-700\">\n";
                $out .= "                        <span class=\"text-xs text-slate-400\">Imagen actual. Sube una nueva para reemplazarla.</span>\n";
                $out .= "                    </div>\n";
                $out .= "                    @endif\n";
            }
        } elseif ($type === 'file') {
            $accept    = trim($field['accept'] ?? '');
            $mimes     = $accept ?: 'pdf,xlsx,xls,docx,doc,zip';
            $acceptAttr = implode(',', array_map(fn($e) => '.' . trim($e), explode(',', $mimes)));
            $out .= "                    <input id=\"{$name}\" type=\"file\" name=\"{$name}\" accept=\"{$acceptAttr}\"{$required} class=\"{$inputClass}\">\n";
            if ($modelVar) {
                $out .= "                    @if(\${$modelVar}->{$name})\n";
                $out .= "                    <div class=\"mt-2\">\n";
                $out .= "                        <a href=\"{{ asset('storage/' . \${$modelVar}->{$name}) }}\" target=\"_blank\" class=\"inline-flex items-center gap-x-1.5 text-xs text-brand-600 hover:underline\">\n";
                $out .= "                            <svg class=\"w-3.5 h-3.5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13\"/></svg>\n";
                $out .= "                            Ver archivo actual\n";
                $out .= "                        </a>\n";
                $out .= "                        <span class=\"ml-2 text-xs text-slate-400\">Sube uno nuevo para reemplazarlo.</span>\n";
                $out .= "                    </div>\n";
                $out .= "                    @endif\n";
            }
        } else {
            $out .= "                    <input id=\"{$name}\" type=\"text\" name=\"{$name}\" value=\"{$oldVal}\"{$required} class=\"{$inputClass}\">\n";
        }

        $out .= "                    @error('{$name}')<p class=\"mt-1 text-xs text-red-500\">{{ \$message }}</p>@enderror\n";
        $out .= "                </div>";

        return $out;
    }

    private function makeRouteSnippet(string $model, bool $dt): string
    {
        $routePrefix = Str::kebab(Str::plural($model));
        $controller  = "{$model}Controller";

        $out  = "use App\\Http\\Controllers\\{$controller};\n\n";
        if ($dt) {
            $out .= "// Ruta para DataTables (debe ir ANTES del resource)\n";
            $out .= "Route::get('/{$routePrefix}/data', [{$controller}::class, 'data'])->name('{$routePrefix}.data');\n";
        }
        $out .= "Route::resource('{$routePrefix}', {$controller}::class)->except(['show']);\n";

        return $out;
    }

    private function injectRoutes(string $model, bool $useDatatables): void
    {
        $webPhp    = base_path('routes/web.php');
        $content   = File::get($webPhp);
        $routeBase = Str::kebab(Str::plural($model));
        $ctrlClass = "{$model}Controller";
        $useImport = "use App\\Http\\Controllers\\{$ctrlClass};";

        // Skip if route already registered
        if (str_contains($content, "'{$routeBase}.index'") || str_contains($content, "'{$routeBase}'")) {
            return;
        }

        // Add use import if not present
        if (!str_contains($content, $useImport)) {
            $content = str_replace(
                'use Illuminate\\Support\\Facades\\Route;',
                $useImport . "\n" . 'use Illuminate\\Support\\Facades\\Route;',
                $content
            );
        }

        // Build route lines
        $entry = "\n    // {$model}\n";
        if ($useDatatables) {
            $entry .= "    Route::get('/{$routeBase}/data', [{$ctrlClass}::class, 'data'])->name('{$routeBase}.data');\n";
        }
        $entry .= "    Route::resource('{$routeBase}', {$ctrlClass}::class)->except(['show']);\n";

        // Insert before marker, fall back to end of group
        $marker = '    // @crud-routes';
        if (str_contains($content, $marker)) {
            $content = str_replace($marker, $entry . $marker, $content);
        } else {
            $content = preg_replace('/(\n\}\);\s*$)/', $entry . '$1', $content);
        }

        File::put($webPhp, $content);
    }

    private function injectMenuItem(string $model, string $label, string $iconPath): void
    {
        $layoutPath = resource_path('views/layouts/app.blade.php');
        $content    = File::get($layoutPath);
        $routeBase  = Str::kebab(Str::plural($model));

        $startMarker = '{{-- @crud-menu-items-start --}}';
        $endMarker   = '{{-- @crud-menu-items-end --}}';

        if (!str_contains($content, $startMarker)) {
            throw new \RuntimeException('Marcador de menú no encontrado en app.blade.php.');
        }

        // Skip if already added
        if (str_contains($content, "route('{$routeBase}.index')")) {
            return;
        }

        $svg  = '<svg class="w-4 h-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">';
        $svg .= '<path stroke-linecap="round" stroke-linejoin="round" d="' . e($iconPath) . '"/>';
        $svg .= '</svg>';

        $item  = "\n            <!-- {$model} -->\n";
        $item .= "            <a href=\"{{ route('{$routeBase}.index') }}\"\n";
        $item .= "               class=\"sidebar-link {{ request()->routeIs('{$routeBase}.*') ? 'active' : '' }}\">\n";
        $item .= "                {$svg}\n";
        $item .= "                {$label}\n";
        $item .= "            </a>";

        // Check if section heading is needed (first item)
        preg_match('/' . preg_quote($startMarker, '/') . '(.*?)' . preg_quote($endMarker, '/') . '/s', $content, $m);
        if (!str_contains($m[1] ?? '', '<a ')) {
            $item = "\n            <div class=\"pt-5 pb-2\">\n"
                  . "                <span class=\"px-3 text-[10px] font-bold uppercase tracking-[0.15em] text-white/50\">Módulos</span>\n"
                  . "            </div>"
                  . $item;
        }

        $content = str_replace($endMarker, $item . "\n            " . $endMarker, $content);
        File::put($layoutPath, $content);
    }

    // ─── Meta helpers ─────────────────────────────────────────────────────────

    private function saveMeta(string $model, array $data): void
    {
        $dir = storage_path('app/' . self::META_DIR);
        File::ensureDirectoryExists($dir);
        File::put("{$dir}/{$model}.json", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function loadMeta(string $model): ?array
    {
        $path = storage_path('app/' . self::META_DIR . '/' . $model . '.json');
        if (!File::exists($path)) return null;
        return json_decode(File::get($path), true);
    }

    private function loadAllMeta(): array
    {
        $dir = storage_path('app/' . self::META_DIR);
        if (!File::isDirectory($dir)) return [];

        return collect(File::files($dir))
            ->filter(fn($f) => $f->getExtension() === 'json')
            ->map(fn($f) => json_decode(File::get($f->getPathname()), true))
            ->filter()
            ->sortByDesc('generated_at')
            ->values()
            ->all();
    }
}
