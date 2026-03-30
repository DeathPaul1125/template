<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class CrudGeneratorController extends Controller
{
    public function index()
    {
        return view('crud-generator.index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'model_name'      => ['required', 'regex:/^[A-Z][a-zA-Z0-9]+$/'],
            'fields'          => ['required', 'array', 'min:1'],
            'fields.*.name'   => ['required', 'regex:/^[a-z][a-z0-9_]*$/'],
            'fields.*.type'   => ['required', 'in:string,text,integer,bigInteger,boolean,date,datetime,decimal,float,enum'],
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

        return back()
            ->with('generated', $generated)
            ->with('route_snippet', $this->makeRouteSnippet($model, $useDatatables))
            ->with('gen_errors', $genErrors)
            ->with('gen_model', $model);
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

        // Validation rules
        $rules = collect($fields)->map(function ($f) {
            $nullable = !empty($f['nullable']);
            $req  = $nullable ? "'nullable'" : "'required'";
            $type = $f['type'];
            $enumVals = trim($f['enum_values'] ?? '');
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

        $onlyFields = collect($fields)->pluck('name')->map(fn($n) => "'{$n}'")->implode(', ');

        $dtImport = $dt ? "\nuse Yajra\\DataTables\\Facades\\DataTables;" : '';

        $out  = "<?php\n\nnamespace App\\Http\\Controllers;\n\n";
        $out .= "use App\\Models\\{$model};\n";
        $out .= "use Illuminate\\Http\\Request;{$dtImport}\n\n";
        $out .= "class {$model}Controller extends Controller\n{\n";

        // index
        if ($dt) {
            $out .= "    public function index()\n    {\n";
            $out .= "        return view('{$viewPrefix}.index');\n    }\n";
            // data()
            $out .= "\n    public function data()\n    {\n";
            $out .= "        \$query = {$model}::query();\n\n";
            $out .= "        return DataTables::of(\$query)\n";
            $out .= "            ->addColumn('actions', function (\${$modelVar}) {\n";
            $out .= "                \$edit = route('{$routePrefix}.edit', \${$modelVar});\n";
            $out .= "                \$del  = route('{$routePrefix}.destroy', \${$modelVar});\n";
            $out .= "                return '<a href=\"' . \$edit . '\" class=\"p-1 text-brand-600 hover:underline text-xs\">Editar</a>'\n";
            $out .= "                     . '<form action=\"' . \$del . '\" method=\"POST\" style=\"display:inline\" onsubmit=\"return confirm(\\'¿Eliminar?\\')\">'";
            $out .= " . csrf_field() . method_field('DELETE')\n";
            $out .= "                     . '<button type=\"submit\" class=\"p-1 text-red-500 hover:underline text-xs ml-2\">Eliminar</button></form>';\n";
            $out .= "            })\n";
            $out .= "            ->rawColumns(['actions'])\n";
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
        $out .= "        {$model}::create(\$request->only([{$onlyFields}]));\n\n";
        $out .= "        return redirect()->route('{$routePrefix}.index')\n";
        $out .= "            ->with('success', '{$titleSingular} creado correctamente.');\n    }\n";

        // edit
        $out .= "\n    public function edit({$model} \${$modelVar})\n    {\n";
        $out .= "        return view('{$viewPrefix}.edit', compact('{$modelVar}'));\n    }\n";

        // update
        $out .= "\n    public function update(Request \$request, {$model} \${$modelVar})\n    {\n";
        $out .= "        \$request->validate([\n{$rules}\n        ]);\n\n";
        $out .= "        \${$modelVar}->update(\$request->only([{$onlyFields}]));\n\n";
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
            fn($f) => "                            <th class=\"px-6 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400\">"
                    . Str::headline($f['name']) . "</th>"
        )->implode("\n");

        $tds = collect($fields)->map(
            fn($f) => "                            <td class=\"px-6 py-4 text-sm text-slate-700 dark:text-slate-300\">{{ \${$modelVar}->{$f['name']} }}</td>"
        )->implode("\n");

        $colCount = count($fields) + 1;

        $out  = "<x-app-layout>\n    <x-slot name=\"header\">{$titlePlural}</x-slot>\n\n";
        $out .= "    <div class=\"space-y-5\">\n";
        $out .= "        <div class=\"flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3\">\n";
        $out .= "            <div>\n";
        $out .= "                <h1 class=\"text-xl font-bold text-slate-800 dark:text-white\">{$titlePlural}</h1>\n";
        $out .= "                <p class=\"text-sm text-slate-500 dark:text-slate-400\">Lista de {$titlePlural}.</p>\n";
        $out .= "            </div>\n";
        $out .= "            <a href=\"{{ route('{$routePrefix}.create') }}\"\n";
        $out .= "               class=\"inline-flex items-center gap-x-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-brand-500/20\">\n";
        $out .= "                <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 4v16m8-8H4\"/></svg>\n";
        $out .= "                Nuevo {$model}\n            </a>\n        </div>\n\n";
        $out .= "        <div class=\"bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden\">\n";
        $out .= "            <div class=\"overflow-x-auto\">\n";
        $out .= "                <table class=\"min-w-full divide-y divide-gray-200 dark:divide-slate-700\">\n";
        $out .= "                    <thead class=\"bg-gray-50 dark:bg-slate-700/50\">\n                        <tr>\n";
        $out .= $ths . "\n";
        $out .= "                            <th class=\"px-6 py-3.5 text-end text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400\">Acciones</th>\n";
        $out .= "                        </tr>\n                    </thead>\n";
        $out .= "                    <tbody class=\"divide-y divide-gray-100 dark:divide-slate-700\">\n";
        $out .= "                        @forelse(\${$modelVarPlural} as \${$modelVar})\n";
        $out .= "                        <tr class=\"hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors\">\n";
        $out .= $tds . "\n";
        $out .= "                            <td class=\"px-6 py-4 text-end\">\n";
        $out .= "                                <div class=\"inline-flex items-center gap-x-1\">\n";
        $out .= "                                    <a href=\"{{ route('{$routePrefix}.edit', \${$modelVar}) }}\" class=\"p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 rounded-lg transition-all\" title=\"Editar\">\n";
        $out .= "                                        <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z\"/></svg>\n";
        $out .= "                                    </a>\n";
        $out .= "                                    <form action=\"{{ route('{$routePrefix}.destroy', \${$modelVar}) }}\" method=\"POST\" onsubmit=\"return confirm('¿Eliminar este registro?')\">\n";
        $out .= "                                        @csrf @method('DELETE')\n";
        $out .= "                                        <button type=\"submit\" class=\"p-2 text-slate-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all\" title=\"Eliminar\">\n";
        $out .= "                                            <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16\"/></svg>\n";
        $out .= "                                        </button>\n                                    </form>\n";
        $out .= "                                </div>\n                            </td>\n";
        $out .= "                        </tr>\n";
        $out .= "                        @empty\n";
        $out .= "                        <tr><td colspan=\"{$colCount}\" class=\"px-6 py-12 text-center text-sm text-slate-500\">No hay registros.</td></tr>\n";
        $out .= "                        @endforelse\n                    </tbody>\n                </table>\n            </div>\n";
        $out .= "            @if(\${$modelVarPlural}->hasPages())\n";
        $out .= "            <div class=\"px-6 py-4 border-t border-gray-200 dark:border-slate-700\">{{ \${$modelVarPlural}->links() }}</div>\n";
        $out .= "            @endif\n        </div>\n    </div>\n</x-app-layout>\n";

        return $out;
    }

    private function makeIndexViewDT(string $model, string $modelVar, string $routePrefix, string $titlePlural, array $fields): string
    {
        $ths = collect($fields)->map(
            fn($f) => "                        <th>" . Str::headline($f['name']) . "</th>"
        )->implode("\n");

        $dtCols = collect($fields)->map(
            fn($f) => "                    { data: '{$f['name']}' }"
        )->implode(",\n");

        $dataRoute   = "{{ route('{$routePrefix}.data') }}";
        $createRoute = "{{ route('{$routePrefix}.create') }}";

        $out  = "<x-app-layout>\n    <x-slot name=\"header\">{$titlePlural}</x-slot>\n\n";
        $out .= "    <div class=\"space-y-5\">\n";
        $out .= "        <div class=\"flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3\">\n";
        $out .= "            <div>\n";
        $out .= "                <h1 class=\"text-xl font-bold text-slate-800 dark:text-white\">{$titlePlural}</h1>\n";
        $out .= "                <p class=\"text-sm text-slate-500 dark:text-slate-400\">Lista de {$titlePlural}.</p>\n";
        $out .= "            </div>\n";
        $out .= "            <a href=\"{$createRoute}\"\n";
        $out .= "               class=\"inline-flex items-center gap-x-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-brand-500/20\">\n";
        $out .= "                <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 4v16m8-8H4\"/></svg>\n";
        $out .= "                Nuevo {$model}\n            </a>\n        </div>\n\n";
        $out .= "        <div class=\"bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden p-4\">\n";
        $out .= "            <table id=\"dt-{$modelVar}\" class=\"min-w-full\" style=\"width:100%\">\n";
        $out .= "                <thead>\n                    <tr>\n";
        $out .= $ths . "\n";
        $out .= "                        <th>Acciones</th>\n";
        $out .= "                    </tr>\n                </thead>\n            </table>\n        </div>\n    </div>\n\n";
        $out .= "@push('scripts')\n<script>\n$(function () {\n";
        $out .= "    \$('#dt-{$modelVar}').DataTable({\n";
        $out .= "        processing: true,\n        serverSide: true,\n";
        $out .= "        ajax: '{$dataRoute}',\n";
        $out .= "        language: { url: '//cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json' },\n";
        $out .= "        columns: [\n";
        $out .= $dtCols . ",\n";
        $out .= "            { data: 'actions', orderable: false, searchable: false }\n";
        $out .= "        ]\n    });\n});\n</script>\n@endpush\n";
        $out .= "</x-app-layout>\n";

        return $out;
    }

    private function makeCreateView(string $model, array $fields): string
    {
        $routePrefix = Str::kebab(Str::plural($model));

        $formFields = collect($fields)->map(fn($f) => $this->makeFormField($f, null))->implode("\n\n");

        $out  = "<x-app-layout>\n";
        $out .= "    <x-slot name=\"header\">Nuevo {$model}</x-slot>\n\n";
        $out .= "    <div class=\"max-w-2xl mx-auto\">\n";
        $out .= "        <div class=\"mb-5\">\n";
        $out .= "            <h1 class=\"text-xl font-bold text-slate-800 dark:text-white\">Crear {$model}</h1>\n";
        $out .= "            <p class=\"text-sm text-slate-500\">Completa el formulario para agregar un nuevo registro.</p>\n";
        $out .= "        </div>\n\n";
        $out .= "        <div class=\"bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm p-6\">\n";
        $out .= "            <form action=\"{{ route('{$routePrefix}.store') }}\" method=\"POST\" class=\"space-y-5\">\n";
        $out .= "                @csrf\n\n";
        $out .= $formFields . "\n\n";
        $out .= "                <div class=\"flex items-center justify-end gap-x-3 pt-2\">\n";
        $out .= "                    <a href=\"{{ route('{$routePrefix}.index') }}\" class=\"py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-xl border border-gray-200 text-slate-700 hover:bg-gray-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700 transition-all\">Cancelar</a>\n";
        $out .= "                    <button type=\"submit\" class=\"py-2.5 px-5 inline-flex items-center gap-x-2 text-sm font-semibold rounded-xl bg-brand-600 hover:bg-brand-700 text-white transition-all shadow-sm shadow-brand-500/20\">\n";
        $out .= "                        <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M5 13l4 4L19 7\"/></svg>\n";
        $out .= "                        Guardar\n                    </button>\n                </div>\n";
        $out .= "            </form>\n        </div>\n    </div>\n</x-app-layout>\n";

        return $out;
    }

    private function makeEditView(string $model, array $fields): string
    {
        $routePrefix = Str::kebab(Str::plural($model));
        $modelVar    = Str::camel($model);

        $formFields = collect($fields)->map(fn($f) => $this->makeFormField($f, $modelVar))->implode("\n\n");

        $out  = "<x-app-layout>\n";
        $out .= "    <x-slot name=\"header\">Editar {$model}</x-slot>\n\n";
        $out .= "    <div class=\"max-w-2xl mx-auto\">\n";
        $out .= "        <div class=\"mb-5\">\n";
        $out .= "            <h1 class=\"text-xl font-bold text-slate-800 dark:text-white\">Editar {$model}</h1>\n";
        $out .= "        </div>\n\n";
        $out .= "        <div class=\"bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm p-6\">\n";
        $out .= "            <form action=\"{{ route('{$routePrefix}.update', \${$modelVar}) }}\" method=\"POST\" class=\"space-y-5\">\n";
        $out .= "                @csrf\n                @method('PUT')\n\n";
        $out .= $formFields . "\n\n";
        $out .= "                <div class=\"flex items-center justify-end gap-x-3 pt-2\">\n";
        $out .= "                    <a href=\"{{ route('{$routePrefix}.index') }}\" class=\"py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-xl border border-gray-200 text-slate-700 hover:bg-gray-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700 transition-all\">Cancelar</a>\n";
        $out .= "                    <button type=\"submit\" class=\"py-2.5 px-5 inline-flex items-center gap-x-2 text-sm font-semibold rounded-xl bg-brand-600 hover:bg-brand-700 text-white transition-all shadow-sm shadow-brand-500/20\">\n";
        $out .= "                        <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M5 13l4 4L19 7\"/></svg>\n";
        $out .= "                        Actualizar\n                    </button>\n                </div>\n";
        $out .= "            </form>\n        </div>\n    </div>\n</x-app-layout>\n";

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
}
