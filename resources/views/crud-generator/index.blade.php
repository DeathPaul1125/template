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
        addRoutes: true,
        addToMenu: true,
        autoMenu: true,
        menuLabel: '',
        selectedIcon: 'M4 6h16M4 10h16M4 14h16M4 18h16',
        icons: [
            { name: 'Lista',        path: 'M4 6h16M4 10h16M4 14h16M4 18h16' },
            { name: 'Cuadrícula',   path: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z' },
            { name: 'Carrito',      path: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z' },
            { name: 'Usuarios',     path: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' },
            { name: 'Maletín',      path: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z' },
            { name: 'Gráficas',     path: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
            { name: 'Calendario',   path: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
            { name: 'Documento',    path: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
            { name: 'Etiqueta',     path: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z' },
            { name: 'Cubo',         path: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' },
            { name: 'Estrella',     path: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z' },
            { name: 'Notificación', path: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' },
            { name: 'Moneda',       path: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
            { name: 'Carpeta',      path: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z' },
            { name: 'Camión',       path: 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1' },
            { name: 'Mapa',         path: 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7' },
            { name: 'Imagen',       path: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z' },
            { name: 'Módulos',      path: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
            { name: 'Engranaje',    path: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' },
        ],
        types: ['string','text','integer','bigInteger','boolean','date','datetime','decimal','float','enum'],

        init() {
            @if(isset($meta))
            this.loadMeta(@json($meta));
            @else
            this.addField();
            @endif
        },

        onModelInput() {
            if (this.autoTable) {
                this.tableName = this.toTableName(this.modelName);
            }
            if (this.autoMenu) {
                this.menuLabel = this.toMenuLabel(this.modelName);
            }
        },

        toMenuLabel(s) {
            if (!s) return '';
            return s.replace(/([A-Z])/g, (m, c, i) => (i > 0 ? ' ' : '') + c).trim();
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

        loadMeta(meta) {
            this.modelName    = meta.model || '';
            this.tableName    = meta.table_name || '';
            this.autoTable    = false;
            this.timestamps   = meta.timestamps !== undefined ? meta.timestamps : true;
            this.softDeletes  = meta.soft_deletes || false;
            this.useDatatables= meta.use_datatables !== undefined ? meta.use_datatables : true;
            this.addRoutes    = meta.add_routes !== undefined ? meta.add_routes : true;
            this.addToMenu    = meta.add_to_menu !== undefined ? meta.add_to_menu : true;
            this.autoMenu     = false;
            this.menuLabel    = meta.menu_label || '';
            this.selectedIcon = meta.menu_icon || 'M4 6h16M4 10h16M4 14h16M4 18h16';
            this.fields       = (meta.fields || []).map(f => ({
                name: f.name || '', type: f.type || 'string',
                nullable: f.nullable || false, searchable: f.searchable !== undefined ? f.searchable : true,
                length: f.length || '', enum_values: f.enum_values || ''
            }));
            if (this.fields.length === 0) this.addField();
        },

        removeField(i) { this.fields.splice(i, 1); },

        hasLength(t) { return ['string','decimal','float'].includes(t); },
        hasEnum(t)   { return t === 'enum'; }
    }">

        {{-- ── Cabecera ── --}}
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">Generador de CRUD</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Crea modelo, migraci&oacute;n, controlador y vistas en segundos. Las rutas y el &iacute;tem del men&uacute; se pueden agregar autom&aacute;ticamente.
            </p>
        </div>

        {{-- ── CRUDs guardados ── --}}
        @if(count($saved ?? []) > 0)
        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
                <div class="flex items-center gap-x-2">
                    <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200">CRUDs Generados <span class="ml-1 text-xs font-normal text-slate-400">({{ count($saved) }})</span></h2>
                </div>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-slate-700">
                @foreach($saved as $s)
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 px-6 py-3.5 hover:bg-gray-50/60 dark:hover:bg-slate-700/30 transition-colors">
                    {{-- Icon --}}
                    <div class="w-8 h-8 rounded-lg bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['menu_icon'] ?? 'M4 6h16M4 10h16M4 14h16M4 18h16' }}"/>
                        </svg>
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ $s['model'] }}</p>
                        <p class="text-xs text-slate-400 truncate">
                            Tabla: <span class="font-mono">{{ $s['table_name'] }}</span>
                            &nbsp;&middot;&nbsp; {{ count($s['fields'] ?? []) }} campos
                            @if($s['use_datatables'] ?? false) &nbsp;&middot;&nbsp; DataTables @endif
                            &nbsp;&middot;&nbsp; {{ $s['generated_at'] ?? '' }}
                        </p>
                    </div>

                    {{-- Badges --}}
                    <div class="hidden sm:flex items-center gap-x-1.5">
                        @if($s['add_routes'] ?? false)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Rutas</span>
                        @endif
                        @if($s['add_to_menu'] ?? false)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400">Men&uacute;</span>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-x-2 ml-auto">
                        {{-- Editar --}}
                        <a href="{{ route('crud-generator.edit', $s['model']) }}"
                           class="inline-flex items-center gap-x-1.5 px-3 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 bg-gray-100 dark:bg-slate-700 hover:bg-brand-50 hover:text-brand-700 dark:hover:bg-slate-600 rounded-lg transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Editar
                        </a>

                        {{-- Reconstruir --}}
                        <form method="POST" action="{{ route('crud-generator.rebuild', $s['model']) }}"
                              onsubmit="return confirm('\u00bfEjecutar migrate + limpiar cach\u00e9 para {{ $s['model'] }}?')">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-x-1.5 px-3 py-1.5 text-xs font-medium text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/40 rounded-lg transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Reconstruir
                            </button>
                        </form>

                        {{-- Eliminar registro --}}
                        <form method="POST" action="{{ route('crud-generator.destroy-meta', $s['model']) }}"
                              onsubmit="return confirm('Eliminar registro de {{ $s['model'] }} de la lista?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all" title="Eliminar registro">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── Info flash (ej: eliminado) ── --}}
        @if(session('info'))
        <div class="flex items-center gap-x-2 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl px-5 py-3">
            <svg class="w-4 h-4 text-slate-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-slate-600 dark:text-slate-300">{{ session('info') }}</p>
        </div>
        @endif

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
                        'route'      => 'R',
                        'menu'       => 'UI',
                        default      => '?',
                    };
                    $color = match($item['type']) {
                        'model'      => 'bg-blue-500',
                        'migration'  => 'bg-amber-500',
                        'controller' => 'bg-blue-500',
                        'view'       => 'bg-teal-600',
                        'route'      => 'bg-green-500',
                        'menu'       => 'bg-violet-500',
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

        {{-- ── Resultado Rebuild ── --}}
        @if(session('rebuild_model'))
        <div class="bg-amber-50 border border-amber-200 dark:bg-amber-900/20 dark:border-amber-700 rounded-2xl p-5 space-y-3">
            <div class="flex items-center gap-x-2">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <p class="text-sm font-semibold text-amber-700 dark:text-amber-300">
                    Reconstrucción de <strong>{{ session('rebuild_model') }}</strong> completada
                </p>
            </div>
            @if(session('rebuild_output'))
            <ul class="space-y-1">
                @foreach(session('rebuild_output') as $line)
                <li class="text-xs text-amber-800 dark:text-amber-200 flex items-start gap-x-2">
                    <svg class="w-3.5 h-3.5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ $line }}
                </li>
                @endforeach
            </ul>
            @endif
            @if(session('rebuild_errors') && count(session('rebuild_errors')))
            <ul class="space-y-1 mt-2">
                @foreach(session('rebuild_errors') as $err)
                <li class="text-xs text-red-600 dark:text-red-300">✕ {{ $err }}</li>
                @endforeach
            </ul>
            @endif
        </div>
        @endif

        {{-- ── Info si viene de edición ── --}}
        @if(isset($meta))
        <div class="flex items-center gap-x-3 bg-blue-50 border border-blue-200 dark:bg-blue-900/20 dark:border-blue-700 rounded-xl px-5 py-3">
            <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-blue-700 dark:text-blue-300">
                Editando <strong>{{ $meta['model'] }}</strong> — al hacer clic en «Generar CRUD» los archivos serán sobreescritos.
            </p>
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

            {{-- Sección 3: Menú y Rutas --}}
            <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm p-6 space-y-5">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wider">3. Men&uacute; y Rutas</h2>

                <div class="flex flex-col gap-5">
                    {{-- Checkbox: agregar rutas --}}
                    <label class="flex items-start gap-x-3 cursor-pointer group">
                        <input type="checkbox" name="add_routes" value="1" x-model="addRoutes"
                               class="mt-0.5 shrink-0 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <div>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Agregar rutas autom&aacute;ticamente</span>
                            <p class="text-xs text-slate-400 mt-0.5">Inserta <code class="bg-slate-100 dark:bg-slate-700 px-1 rounded">Route::resource</code> en <code class="bg-slate-100 dark:bg-slate-700 px-1 rounded">routes/web.php</code> de forma autom&aacute;tica.</p>
                        </div>
                    </label>

                    {{-- Checkbox: agregar al menú --}}
                    <label class="flex items-start gap-x-3 cursor-pointer group">
                        <input type="checkbox" name="add_to_menu" value="1" x-model="addToMenu"
                               class="mt-0.5 shrink-0 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <div>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Agregar &iacute;tem al men&uacute; lateral</span>
                            <p class="text-xs text-slate-400 mt-0.5">Inserta un enlace en la secci&oacute;n &laquo;M&oacute;dulos&raquo; del sidebar autom&aacute;ticamente.</p>
                        </div>
                    </label>

                    {{-- Opciones del menú (visible solo si addToMenu) --}}
                    <div x-show="addToMenu" x-transition class="border-t border-gray-100 dark:border-slate-700 pt-5 space-y-5 pl-7">
                        {{-- Etiqueta --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                Etiqueta en el men&uacute;
                                <span class="ml-1 text-xs text-slate-400 font-normal">(nombre visible en el sidebar)</span>
                            </label>
                            <input type="text" name="menu_label"
                                   x-model="menuLabel"
                                   @input="autoMenu = false"
                                   placeholder="ej: Productos"
                                   class="py-2.5 px-4 block w-full max-w-xs border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300">
                        </div>

                        {{-- Selector de ícono --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-300">&Iacute;cono del men&uacute;</p>
                                <a href="{{ route('crud-generator.icons') }}" target="_blank"
                                   class="text-xs text-brand-600 hover:underline dark:text-brand-400 flex items-center gap-x-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    Ver todos los c&oacute;digos
                                </a>
                            </div>
                            <input type="hidden" name="menu_icon" :value="selectedIcon">
                            <div class="flex items-center gap-x-3">
                                {{-- Preview --}}
                                <div class="w-9 h-9 rounded-lg bg-brand-100 dark:bg-slate-700 flex items-center justify-center flex-shrink-0 border border-brand-200 dark:border-slate-600">
                                    <svg class="w-5 h-5 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" :d="selectedIcon"/>
                                    </svg>
                                </div>
                                {{-- Select --}}
                                <select x-model="selectedIcon"
                                        @change="icons.find(i => i.path === selectedIcon)"
                                        class="py-2 px-3 block w-full max-w-xs border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300">
                                    <template x-for="icon in icons" :key="icon.name">
                                        <option :value="icon.path" x-text="icon.name" :selected="selectedIcon === icon.path"></option>
                                    </template>
                                    <option value="custom">── Personalizado ──</option>
                                </select>
                            </div>
                            {{-- Input manual --}}
                            <div x-show="selectedIcon === 'custom'" x-transition class="mt-2">
                                <input type="text"
                                       placeholder="Pega el path SVG del ícono (d=&quot;...&quot;)"
                                       @input="selectedIcon = $event.target.value || 'M4 6h16M4 10h16M4 14h16M4 18h16'"
                                       class="py-2 px-3 block w-full border-gray-200 rounded-xl text-xs font-mono focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300">
                            </div>
                        </div>
                    </div>
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
                    Los archivos existentes ser&aacute;n sobreescritos.
                    <span x-show="!addRoutes" class="ml-1">Recuerda agregar la ruta manualmente.</span>
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
