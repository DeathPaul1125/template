<?php
$file = __DIR__ . '/../app/Http/Controllers/CrudGeneratorController.php';
$content = file_get_contents($file);

// ---- NEW makeIndexView ----
$newIndexView = <<<'NEWIV'
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
            $val = "{{ \${$modelVar}->{$f['name']} }}";
            if ($f['type'] === 'boolean') {
                return "                            <td class=\"px-5 py-3.5\">\n"
                    . "                                <span class=\"inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ \${$modelVar}->{$f['name']} ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}\">\n"
                    . "                                    {{ \${$modelVar}->{$f['name']} ? 'S\u00ed' : 'No' }}\n"
                    . "                                </span>\n"
                    . "                            </td>";
            }
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
        $out .= "                        @forelse(\${$modelVarPlural} as \${$modelVar})\n";
        $out .= "                        <tr class=\"hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors group\">\n";
        $out .= "                            <td class=\"px-5 py-3.5 text-sm font-mono text-slate-400\">{{ \${$modelVar}->id }}</td>\n";
        $out .= $tds . "\n";
        $out .= "                            <td class=\"px-5 py-3.5 text-right\">\n";
        $out .= "                                <div class=\"inline-flex items-center gap-x-1 opacity-0 group-hover:opacity-100 transition-opacity\">\n";
        $out .= "                                    <a href=\"{{ route('{$routePrefix}.edit', \${$modelVar}) }}\"\n";
        $out .= "                                       class=\"p-1.5 text-slate-400 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 rounded-lg transition-all\" title=\"Editar\">\n";
        $out .= "                                        <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z\"/></svg>\n";
        $out .= "                                    </a>\n";
        $out .= "                                    <form action=\"{{ route('{$routePrefix}.destroy', \${$modelVar}) }}\" method=\"POST\"\n";
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
        $out .= "            @if(\${$modelVarPlural}->hasPages())\n";
        $out .= "            <div class=\"px-5 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30\">\n";
        $out .= "                {{ \${$modelVarPlural}->links() }}\n";
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

        $dtCols = collect($fields)->map(
            fn($f) => "                    { data: '{$f['name']}', className: 'px-4 py-3 text-sm text-slate-700' }"
        )->implode(",\n");

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
        $out .= "            <div class=\"p-4\">\n";
        $out .= "            <table id=\"dt-{$modelVar}\" class=\"min-w-full\" style=\"width:100%\">\n";
        $out .= "                <thead>\n";
        $out .= "                    <tr class=\"border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50\">\n";
        $out .= "                        <th class=\"px-4 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400\">#</th>\n";
        $out .= $ths . "\n";
        $out .= "                        <th class=\"px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400\">Acciones</th>\n";
        $out .= "                    </tr>\n                </thead>\n            </table>\n            </div>\n        </div>\n    </div>\n\n";
        $out .= "@push('scripts')\n<script>\n$(function () {\n";
        $out .= "    \$('#dt-{$modelVar}').DataTable({\n";
        $out .= "        processing: true,\n        serverSide: true,\n";
        $out .= "        ajax: '{$dataRoute}',\n";
        $out .= "        language: { url: '//cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json' },\n";
        $out .= "        columns: [\n";
        $out .= "            { data: 'id', className: 'px-4 py-3 text-sm font-mono text-slate-400' },\n";
        $out .= $dtCols . ",\n";
        $out .= "            { data: 'actions', orderable: false, searchable: false, className: 'px-4 py-3 text-right' }\n";
        $out .= "        ]\n    });\n});\n</script>\n@endpush\n";
        $out .= "</x-app-layout>\n";

        return $out;
    }

NEWIV;

// Use regex to replace between makeIndexView start and makeCreateView start
$pattern = '/(    private function makeIndexView\(string \$model.*?)(    private function makeCreateView\()/s';

if (preg_match($pattern, $content)) {
    $new = preg_replace($pattern, $newIndexView . '    private function makeCreateView(', $content, 1);
    file_put_contents($file, $new);
    echo "SUCCESS: replaced makeIndexView + makeIndexViewDT\n";
    echo "Old size: " . strlen($content) . ", new size: " . strlen($new) . "\n";
} else {
    echo "FAILED: pattern not found\n";
}
