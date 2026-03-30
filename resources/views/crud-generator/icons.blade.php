<x-app-layout>
    <x-slot name="header">Referencia de Íconos</x-slot>

    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-800 dark:text-white">Referencia de Íconos — Heroicons</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Copia el valor del campo <code class="bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded text-xs">path d=</code> y pégalo en el campo «path personalizado» del Generador CRUD.
                </p>
            </div>
            <a href="{{ route('crud-generator.index') }}"
               class="inline-flex items-center gap-x-2 px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 rounded-xl transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver al Generador
            </a>
        </div>

        @php
        $icons = [
            // ── Navegación / UI ──────────────────────────────────────
            ['cat' => 'Navegación', 'name' => 'Lista',           'path' => 'M4 6h16M4 10h16M4 14h16M4 18h16'],
            ['cat' => 'Navegación', 'name' => 'Tabla / Grid',    'path' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
            ['cat' => 'Navegación', 'name' => 'Dashboard',       'path' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['cat' => 'Navegación', 'name' => 'Menú hamburguesa','path' => 'M4 6h16M4 12h16M4 18h16'],
            ['cat' => 'Navegación', 'name' => 'Módulos / Layers','path' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
            ['cat' => 'Navegación', 'name' => 'Código / Dev',    'path' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],

            // ── Personas ─────────────────────────────────────────────
            ['cat' => 'Personas',   'name' => 'Usuario',         'path' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
            ['cat' => 'Personas',   'name' => 'Usuarios / Grupo','path' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
            ['cat' => 'Personas',   'name' => 'ID / Badge',      'path' => 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2'],

            // ── Comercio ─────────────────────────────────────────────
            ['cat' => 'Comercio',   'name' => 'Carrito',         'path' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
            ['cat' => 'Comercio',   'name' => 'Bolsa de compras','path' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
            ['cat' => 'Comercio',   'name' => 'Tag / Precio',    'path' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
            ['cat' => 'Comercio',   'name' => 'Dinero / Moneda', 'path' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['cat' => 'Comercio',   'name' => 'Cupón',           'path' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
            ['cat' => 'Comercio',   'name' => 'Crédito / Tarjeta','path' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],

            // ── Logística ─────────────────────────────────────────────
            ['cat' => 'Logística',  'name' => 'Camión',          'path' => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1'],
            ['cat' => 'Logística',  'name' => 'Cubo / Producto', 'path' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
            ['cat' => 'Logística',  'name' => 'Almacén',         'path' => 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z'],
            ['cat' => 'Logística',  'name' => 'Mapa / Ubicación','path' => 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7'],
            ['cat' => 'Logística',  'name' => 'Pin / Lugar',     'path' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z'],

            // ── Documentos ───────────────────────────────────────────
            ['cat' => 'Documentos', 'name' => 'Documento',       'path' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['cat' => 'Documentos', 'name' => 'Documentos múlt.','path' => 'M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2'],
            ['cat' => 'Documentos', 'name' => 'Carpeta',         'path' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'],
            ['cat' => 'Documentos', 'name' => 'Portapapeles',    'path' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
            ['cat' => 'Documentos', 'name' => 'PDF / Informe',   'path' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z'],

            // ── Calendario / Tiempo ──────────────────────────────────
            ['cat' => 'Tiempo',     'name' => 'Calendario',      'path' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['cat' => 'Tiempo',     'name' => 'Reloj',           'path' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['cat' => 'Tiempo',     'name' => 'Historial',       'path' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],

            // ── Datos / Análisis ─────────────────────────────────────
            ['cat' => 'Datos',      'name' => 'Gráfica barras',  'path' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['cat' => 'Datos',      'name' => 'Tendencia',       'path' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
            ['cat' => 'Datos',      'name' => 'Base de datos',   'path' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4'],
            ['cat' => 'Datos',      'name' => 'Filtro',          'path' => 'M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z'],

            // ── Comunicación ─────────────────────────────────────────
            ['cat' => 'Comunicación','name' => 'Email',          'path' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ['cat' => 'Comunicación','name' => 'Chat',           'path' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
            ['cat' => 'Comunicación','name' => 'Notificación',   'path' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
            ['cat' => 'Comunicación','name' => 'Teléfono',       'path' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'],

            // ── Sistema / Admin ──────────────────────────────────────
            ['cat' => 'Sistema',    'name' => 'Engranaje',       'path' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
            ['cat' => 'Sistema',    'name' => 'Candado',         'path' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
            ['cat' => 'Sistema',    'name' => 'Escudo / Roles',  'path' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
            ['cat' => 'Sistema',    'name' => 'Reciclar / Reset','path' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
            ['cat' => 'Sistema',    'name' => 'Subir / Upload',  'path' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12'],
            ['cat' => 'Sistema',    'name' => 'Descargar',       'path' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4'],
            ['cat' => 'Sistema',    'name' => 'Maletín / Tareas','path' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ['cat' => 'Sistema',    'name' => 'Imagen',          'path' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['cat' => 'Sistema',    'name' => 'Estrella',        'path' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
        ];

        $grouped = collect($icons)->groupBy('cat');
        @endphp

        @foreach($grouped as $cat => $items)
        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-3.5 border-b border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
                <h2 class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $cat }}</h2>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-slate-700/60">
                @foreach($items as $icon)
                <div x-data="{ copied: false }"
                     class="flex items-center gap-x-4 px-6 py-3 hover:bg-gray-50/70 dark:hover:bg-slate-700/30 transition-colors group">
                    {{-- Preview --}}
                    <div class="w-9 h-9 rounded-lg bg-brand-100 dark:bg-slate-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon['path'] }}"/>
                        </svg>
                    </div>

                    {{-- Name --}}
                    <p class="w-40 flex-shrink-0 text-sm font-medium text-slate-700 dark:text-slate-300">{{ $icon['name'] }}</p>

                    {{-- Path --}}
                    <code class="flex-1 text-xs font-mono text-slate-500 dark:text-slate-400 truncate">{{ $icon['path'] }}</code>

                    {{-- Copy button --}}
                    <button type="button"
                            @click="navigator.clipboard.writeText('{{ addslashes($icon['path']) }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                            class="flex-shrink-0 inline-flex items-center gap-x-1.5 px-3 py-1.5 text-xs font-medium rounded-lg transition-all"
                            :class="copied
                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                : 'bg-gray-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 hover:bg-brand-50 hover:text-brand-700 dark:hover:bg-slate-600 opacity-0 group-hover:opacity-100'">
                        <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                        </svg>
                        <svg x-show="copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span x-text="copied ? '¡Copiado!' : 'Copiar'"></span>
                    </button>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        {{-- Instrucciones de uso --}}
        <div class="bg-slate-50 dark:bg-slate-800/50 border border-gray-200 dark:border-slate-700 rounded-2xl p-6">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">¿Cómo usar un ícono personalizado?</h3>
            <ol class="space-y-2 text-sm text-slate-600 dark:text-slate-400 list-decimal list-inside">
                <li>Copia el path del ícono deseado con el botón <span class="font-medium">Copiar</span>.</li>
                <li>En el Generador CRUD, en el selector de ícono elige <span class="font-mono bg-slate-100 dark:bg-slate-700 px-1 rounded">── Personalizado ──</span>.</li>
                <li>Pega el path en el campo de texto que aparece debajo.</li>
                <li>El preview se actualizará en tiempo real.</li>
            </ol>
            <p class="mt-4 text-xs text-slate-400">
                Los íconos son de <strong>Heroicons v2</strong> (outline). Puedes encontrar más en
                <a href="https://heroicons.com" target="_blank" class="text-brand-500 hover:underline">heroicons.com</a>
                y copiar el atributo <code class="bg-slate-100 dark:bg-slate-700 px-1 rounded">d="..."</code> del elemento <code class="bg-slate-100 dark:bg-slate-700 px-1 rounded">&lt;path&gt;</code>.
            </p>
        </div>
    </div>
</x-app-layout>
