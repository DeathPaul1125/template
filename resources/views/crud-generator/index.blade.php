<x-app-layout>
    <x-slot name="header">Generador de CRUD</x-slot>

    <div class="space-y-6" x-data="{
        modelName: '',
        tableName: '',
        autoTable: true,
        timestamps: true,
        softDeletes: false,
        useDatatables: true,
        fields: [],
        types: ['string','text','integer','bigInteger','boolean','date','datetime','decimal','float','enum'],

        init() { this.addField(); },

        onModelInput() {
            if (this.autoTable) {
                this.tableName = this.toTableName(this.modelName);
            }
        },

        toTableName(s) {
            if (!s) return '';
            const snake = s.replace(/([A-Z])/g, (m, c, i) => (i > 0 ? '_' : '') + c.toLowerCase());
            const plural = snake.replace(/y$/, 'ies').replace(/([^aeiou])$/, (m) => m === 's' ? 'ss' : m + 's');
            return plural;
        },

        addField() {
            this.fields.push({ name: '', type: 'string', nullable: false, searchable: true, length: '', enum_values: '' });
        },

        removeField(i) { this.fields.splice(i, 1); },

        hasLength(t) { return ['string','decimal','float'].includes(t); },
        hasEnum(t)   { return t === 'enum'; }
    }">

        {{-- ── Cabecera ── --}}
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">Generador de CRUD</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Crea modelo, migración, controlador y vistas en segundos. Solo se necesita agregar la ruta manualmente.
            </p>
        </div>

        {{-- ── Resultado de generación ── --}}
        @if(session('generated'))
        <div class="bg-teal-50 border border-teal-200 dark:bg-teal-900/20 dark:border-teal-700 rounded-2xl p-5 space-y-4">
            <div class="flex items-center gap-x-2">
                <svg class="w-5 h-5 text-teal-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm font-semibold text-teal-700 dark:text-teal-300">
                    {{ session('gen_model') }} generado correctamente — {{ count(session('generated')) }} archivos creados
                </p>
            </div>

            <ul class="grid sm:grid-cols-2 gap-2">
                @foreach(session('generated') as $item)
                <li class="flex items-center gap-x-2 text-xs text-teal-800 dark:text-teal-200 bg-teal-100 dark:bg-teal-900/40 rounded-lg px-3 py-2 font-mono">
                    @php
                    $icon = match($item['type']) {
                        'model'      => 'M',
                        'migration'  => 'DB',
                        'controller' => 'C',
                        'view'       => 'V',
                        default      => '?',
                    };
                    $color = match($item['type']) {
                        'model'      => 'bg-blue-500',
                        'migration'  => 'bg-amber-500',
                        'controller' => 'bg-purple-500',
                        'view'       => 'bg-teal-600',
                        default      => 'bg-slate-500',
                    };
                    @endphp
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded text-white text-[10px] font-bold flex-shrink-0 {{ $color }}">{{ $icon }}</span>
                    {{ $item['file'] }}
                </li>
                @endforeach
            </ul>

            @if(session('route_snippet'))
            <div>
                <p class="text-xs font-semibold text-teal-700 dark:text-teal-300 mb-2 uppercase tracking-wider">Agrega esta ruta en <code>routes/web.php</code>:</p>
                <div class="relative bg-slate-900 rounded-xl overflow-hidden">
                    <pre class="text-xs text-slate-300 p-4 overflow-x-auto leading-relaxed"><code>{{ session('route_snippet') }}</code></pre>
                    <button onclick="navigator.clipboard.writeText(this.previousElementSibling.querySelector('code').textContent).then(() => { this.textContent = '¡Copiado!'; setTimeout(() => this.textContent = 'Copiar', 2000); })"
                            class="absolute top-3 right-3 text-xs bg-white/10 hover:bg-white/20 text-slate-300 px-2.5 py-1 rounded-lg transition-colors">
                        Copiar
                    </button>
                </div>
            </div>
            @endif
        </div>
        @endif

        @if(session('gen_errors') && count(session('gen_errors')))
        <div class="bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-700 rounded-2xl p-4">
            <p class="text-sm font-semibold text-red-700 dark:text-red-400 mb-2">Errores durante la generación:</p>
            <ul class="space-y-1">
                @foreach(session('gen_errors') as $err)
                <li class="text-xs text-red-600 dark:text-red-300">• {{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- ── Formulario ── --}}
        <form action="{{ route('crud-generator.generate') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Nombre y tabla --}}
            <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm p-6 space-y-5">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wider">1. Configuración básica</h2>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Nombre del modelo
                            <span class="ml-1 text-xs text-slate-400 font-normal">(PascalCase, ej: Product)</span>
                        </label>
                        <input type="text" name="model_name"
                               x-model="modelName"
                               @input="onModelInput()"
                               placeholder="Product"
                               required pattern="[A-Z][a-zA-Z0-9]+"
                               class="py-3 px-4 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300 @error('model_name') border-red-500 @enderror">
                        @error('model_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            Nombre de la tabla
                            <span class="ml-1 text-xs text-slate-400 font-normal">(snake_case, auto-generado)</span>
                        </label>
                        <input type="text" name="table_name"
                               x-model="tableName"
                               @input="autoTable = false"
                               placeholder="products"
                               class="py-3 px-4 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300">
                    </div>
                </div>

                {{-- Opciones --}}
                <div>
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">Opciones</p>
                    <div class="flex flex-wrap gap-6">
                        <label class="flex items-center gap-x-2 cursor-pointer">
                            <input type="checkbox" name="timestamps" value="1"
                                   x-model="timestamps"
                                   class="shrink-0 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span class="text-sm text-slate-600 dark:text-slate-400">Timestamps <span class="text-xs text-slate-400">(created_at, updated_at)</span></span>
                        </label>
                        <label class="flex items-center gap-x-2 cursor-pointer">
                            <input type="checkbox" name="soft_deletes" value="1"
                                   x-model="softDeletes"
                                   class="shrink-0 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span class="text-sm text-slate-600 dark:text-slate-400">Soft Deletes <span class="text-xs text-slate-400">(deleted_at)</span></span>
                        </label>
                        <label class="flex items-center gap-x-2 cursor-pointer">
                            <input type="checkbox" name="use_datatables" value="1"
                                   x-model="useDatatables"
                                   class="shrink-0 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span class="text-sm text-slate-600 dark:text-slate-400">Usar DataTables <span class="text-xs text-slate-400">(server-side)</span></span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Campos --}}
            <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-slate-700">
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wider">2. Campos</h2>
                    <button type="button" @click="addField()"
                            class="inline-flex items-center gap-x-1.5 px-3 py-1.5 text-xs font-semibold bg-brand-600 hover:bg-brand-700 text-white rounded-lg transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Agregar campo
                    </button>
                </div>

                @error('fields')<p class="px-6 py-2 text-xs text-red-500 bg-red-50">{{ $message }}</p>@enderror
                @error('fields.*.name')<p class="px-6 py-2 text-xs text-red-500 bg-red-50">Nombre de campo inválido (solo minúsculas, números y guión bajo).</p>@enderror

                {{-- Campo vacío --}}
                <div x-show="fields.length === 0" class="px-6 py-10 text-center">
                    <p class="text-sm text-slate-400">No hay campos definidos. Haz clic en «Agregar campo».</p>
                </div>

                {{-- Tabla de campos --}}
                <div x-show="fields.length > 0" class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 dark:bg-slate-700/50 border-b border-gray-100 dark:border-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-48">Nombre del campo</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-40">Tipo</th>
                                <th class="px-4 py-3 text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Largo / Valores</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-24">Nullable</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 w-24">Buscable</th>
                                <th class="w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            <template x-for="(field, index) in fields" :key="index">
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-700/20">
                                    {{-- Nombre --}}
                                    <td class="px-4 py-3">
                                        <input type="text"
                                               :name="'fields[' + index + '][name]'"
                                               x-model="field.name"
                                               placeholder="nombre_campo"
                                               required
                                               pattern="[a-z][a-z0-9_]*"
                                               class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300">
                                    </td>

                                    {{-- Tipo --}}
                                    <td class="px-4 py-3">
                                        <select :name="'fields[' + index + '][type]'"
                                                x-model="field.type"
                                                class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300">
                                            <template x-for="t in types" :key="t">
                                                <option :value="t" x-text="t"></option>
                                            </template>
                                        </select>
                                    </td>

                                    {{-- Largo / Enum values --}}
                                    <td class="px-4 py-3">
                                        <input x-show="hasLength(field.type)"
                                               type="text"
                                               :name="'fields[' + index + '][length]'"
                                               x-model="field.length"
                                               :placeholder="field.type === 'string' ? 'ej: 100' : 'ej: 8,2'"
                                               class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300">
                                        <input x-show="hasEnum(field.type)"
                                               type="text"
                                               :name="'fields[' + index + '][enum_values]'"
                                               x-model="field.enum_values"
                                               placeholder="activo,inactivo,suspendido"
                                               class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300">
                                        <span x-show="!hasLength(field.type) && !hasEnum(field.type)"
                                              class="text-xs text-slate-400 px-1">—</span>
                                    </td>

                                    {{-- Nullable --}}
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox"
                                               :name="'fields[' + index + '][nullable]'"
                                               value="1"
                                               x-model="field.nullable"
                                               class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                    </td>

                                    {{-- Searchable --}}
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox"
                                               :name="'fields[' + index + '][searchable]'"
                                               value="1"
                                               x-model="field.searchable"
                                               class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                    </td>

                                    {{-- Eliminar --}}
                                    <td class="px-4 py-3 text-center">
                                        <button type="button" @click="removeField(index)"
                                                class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Referencia de tipos --}}
            <details class="bg-slate-50 dark:bg-slate-800/50 border border-gray-200 dark:border-slate-700 rounded-2xl">
                <summary class="px-5 py-3.5 text-sm font-medium text-slate-600 dark:text-slate-400 cursor-pointer select-none list-none flex items-center gap-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Referencia de tipos de campo
                </summary>
                <div class="px-5 pb-4 pt-2 grid sm:grid-cols-2 lg:grid-cols-3 gap-2 text-xs">
                    @foreach([
                        'string'     => 'VARCHAR (con largo opcional, ej: 100)',
                        'text'       => 'TEXT largo (textarea en el form)',
                        'integer'    => 'INT número entero',
                        'bigInteger' => 'BIGINT número entero grande',
                        'boolean'    => 'TINYINT(1) true/false (checkbox)',
                        'date'       => 'DATE solo fecha',
                        'datetime'   => 'DATETIME fecha y hora',
                        'decimal'    => 'DECIMAL precisión fija (largo: "8,2")',
                        'float'      => 'FLOAT punto flotante (largo: "8,2")',
                        'enum'       => 'ENUM valores separados por coma',
                    ] as $t => $desc)
                    <div class="flex gap-x-2">
                        <code class="font-semibold text-brand-600 dark:text-brand-400 w-24 flex-shrink-0">{{ $t }}</code>
                        <span class="text-slate-500 dark:text-slate-400">{{ $desc }}</span>
                    </div>
                    @endforeach
                </div>
            </details>

            {{-- Botón generate --}}
            <div class="flex items-center justify-between">
                <p class="text-xs text-slate-400">
                    <svg class="w-3.5 h-3.5 inline-block mr-1 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    Los archivos existentes serán sobreescritos. Recuerda agregar la ruta manualmente.
                </p>
                <button type="submit"
                        :disabled="!modelName || fields.length === 0"
                        class="inline-flex items-center gap-x-2 px-6 py-3 bg-brand-600 hover:bg-brand-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-brand-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Generar CRUD
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
