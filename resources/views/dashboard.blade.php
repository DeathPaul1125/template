<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
    // ── Dashboard builder ───────────────────────────────────────────────────
    function dashboard(saved, available) {
        return {
            widgets:   saved,
            available: available,
            editMode:  false,
            addOpen:   false,
            saving:    false,
            savedOk:   false,

            // New widget form state
            step: 1,
            nw: {},

            init() { this.resetNw(); },

            resetNw() {
                this.nw = {
                    id:        'w_' + Date.now(),
                    type:      'stat',
                    title:     '',
                    model:     this.available[0]?.model || '',
                    field:     null,
                    operation: 'count',
                    limit:     10,
                    size:      2,
                    color:     'blue',
                };
                this.step = 1;
            },

            currentModel() {
                return this.available.find(a => a.model === this.nw.model) || null;
            },

            modelFields() {
                return this.currentModel()?.fields || [];
            },

            isChart() {
                return ['bar-chart', 'pie-chart', 'line-chart'].includes(this.nw.type);
            },

            suggestSize() {
                const map = { stat: 1, 'recent-table': 4, 'line-chart': 4, 'bar-chart': 2, 'pie-chart': 2 };
                this.nw.size = map[this.nw.type] || 2;
            },

            pickType(t) {
                this.nw.type = t;
                this.suggestSize();
                this.nw.field = this.modelFields()[0] || null;
                this.step = 2;
            },

            colClass(size) {
                const map = { 1: 'col-span-4 lg:col-span-1', 2: 'col-span-4 sm:col-span-2', 3: 'col-span-4 sm:col-span-2 lg:col-span-3', 4: 'col-span-4' };
                return map[size] || map[2];
            },

            openAdd() {
                this.resetNw();
                this.addOpen = true;
            },

            addWidget() {
                if (!this.nw.title.trim()) {
                    const m = this.currentModel();
                    const typeLabel = { stat: 'Tarjeta', 'recent-table': 'Tabla', 'line-chart': 'Linea', 'bar-chart': 'Barras', 'pie-chart': 'Torta' };
                    this.nw.title = (m?.label || this.nw.model) + ' - ' + (typeLabel[this.nw.type] || this.nw.type);
                }
                this.widgets.push({ ...this.nw });
                this.addOpen = false;
                this.save();
            },

            removeWidget(id) {
                this.widgets = this.widgets.filter(w => w.id !== id);
                this.save();
            },

            moveWidget(index, dir) {
                const target = index + dir;
                if (target < 0 || target >= this.widgets.length) return;
                [this.widgets[index], this.widgets[target]] = [this.widgets[target], this.widgets[index]];
                this.save();
            },

            async save() {
                this.saving = true;
                try {
                    await fetch('/dashboard/save-layout', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ widgets: this.widgets })
                    });
                    this.savedOk = true;
                    setTimeout(() => this.savedOk = false, 2500);
                } finally {
                    this.saving = false;
                }
            }
        };
    }

    // ── Individual widget data component ───────────────────────────────────
    function widgetComp(cfg) {
        return {
            cfg:     cfg,
            data:    null,
            loading: true,
            error:   null,
            chart:   null,

            isChart() {
                return ['bar-chart', 'pie-chart', 'line-chart'].includes(this.cfg.type);
            },

            async init() {
                await this.load();
            },

            async load() {
                this.loading = true;
                this.error   = null;
                try {
                    const res = await fetch('/dashboard/widget-data', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(this.cfg)
                    });
                    const json = await res.json();
                    if (json.error) {
                        this.error = json.error;
                    } else {
                        this.data = json;
                        if (this.isChart()) {
                            await this.$nextTick();
                            this.initChart();
                        }
                    }
                } catch (e) {
                    this.error = 'Error de red.';
                } finally {
                    this.loading = false;
                }
            },

            initChart() {
                const canvas = this.$el.querySelector('canvas');
                if (!canvas || !this.data) return;
                if (this.chart) this.chart.destroy();

                const palette = ['#0e8ceb','#026ec7','#38a9f8','#7cc8fb','#10b981','#059669','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16'];
                const isDark  = document.documentElement.classList.contains('dark');
                const gridColor = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.06)';
                const textColor = isDark ? '#94a3b8' : '#64748b';

                const type = this.cfg.type === 'bar-chart' ? 'bar'
                           : this.cfg.type === 'pie-chart'  ? 'doughnut'
                           : 'line';

                const isDonut = type === 'doughnut';

                this.chart = new Chart(canvas, {
                    type,
                    data: {
                        labels: this.data.labels,
                        datasets: [{
                            label: this.cfg.title,
                            data:  this.data.values,
                            backgroundColor: isDonut ? palette : palette[0] + '33',
                            borderColor:     isDonut ? palette.map(c => c + 'cc') : palette[0],
                            borderWidth: 2,
                            tension:     0.4,
                            fill:        type === 'line',
                            pointRadius: type === 'line' ? 3 : 0,
                            pointHoverRadius: type === 'line' ? 5 : 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: isDonut, position: 'bottom', labels: { color: textColor, padding: 12, font: { size: 11 } } },
                        },
                        scales: !isDonut ? {
                            x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } },
                            y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } }
                        } : undefined,
                    }
                });
            }
        };
    }
    </script>
    @endpush

    {{-- Tailwind safelist hint for JIT - DO NOT REMOVE --}}
    {{-- col-span-1 col-span-2 col-span-3 col-span-4 sm:col-span-2 lg:col-span-1 lg:col-span-2 lg:col-span-3 lg:col-span-4 bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400 bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400 bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400 bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400 ring-blue-500 ring-emerald-500 ring-amber-500 ring-rose-500 ring-violet-500 ring-sky-500 bg-blue-500 bg-emerald-500 bg-amber-500 bg-rose-500 bg-violet-500 bg-sky-500 --}}

    <div x-data="dashboard(@json($layout), @json($models))">

        {{-- Header bar --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-800 dark:text-white">Dashboard</h1>
                <p class="text-xs text-slate-400 mt-0.5" x-show="editMode">Modo de edicion activo.</p>
            </div>
            <div class="flex items-center gap-x-3">
                <span x-show="savedOk" x-transition class="text-xs text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-x-1">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Guardado
                </span>

                @role('super-admin')
                <button x-show="editMode" @click="openAdd()"
                        class="inline-flex items-center gap-x-1.5 px-4 py-2 text-sm font-semibold bg-brand-600 hover:bg-brand-700 text-white rounded-xl transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Agregar widget
                </button>

                <button @click="editMode = !editMode"
                        :class="editMode
                            ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 ring-1 ring-amber-300 dark:ring-amber-700'
                            : 'bg-gray-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-gray-200 dark:hover:bg-slate-600'"
                        class="inline-flex items-center gap-x-1.5 px-4 py-2 text-sm font-medium rounded-xl transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span x-text="editMode ? 'Salir del editor' : 'Editar dashboard'"></span>
                </button>
                @endrole
            </div>
        </div>

        {{-- Empty state --}}
        <div x-show="widgets.length === 0" class="flex flex-col items-center justify-center py-32 text-center">
            <div class="w-20 h-20 rounded-3xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-6">
                <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
            </div>
            <p class="text-base font-semibold text-slate-600 dark:text-slate-300 mb-1">Dashboard vacio</p>
            <p class="text-sm text-slate-400 mb-6 max-w-xs">
                @role('super-admin')
                Activa «Editar dashboard» y agrega tarjetas, tablas y graficas con datos de tus modelos.
                @else
                El administrador aun no ha configurado el dashboard.
                @endrole
            </p>
            @role('super-admin')
            <button @click="editMode = true; openAdd()"
                    class="inline-flex items-center gap-x-2 px-5 py-2.5 text-sm font-semibold bg-brand-600 hover:bg-brand-700 text-white rounded-xl shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Agregar primer widget
            </button>
            @endrole
        </div>

        {{-- Widget grid --}}
        <div x-show="widgets.length > 0" class="grid grid-cols-4 gap-6">
            <template x-for="(w, i) in widgets" :key="w.id">
                <div :class="colClass(w.size)"
                     x-data="widgetComp(w)"
                     class="relative group">

                    {{-- Edit overlay controls --}}
                    <template x-teleport="body">
                        <div style="display:none"></div>
                    </template>
                    <div x-show="$root.__x.$data.editMode"
                         class="absolute top-2 right-2 z-10 flex items-center gap-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="$root.__x.$data.moveWidget(i, -1)"
                                class="p-1.5 bg-white dark:bg-slate-700 rounded-lg shadow text-slate-400 hover:text-brand-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        </button>
                        <button @click="$root.__x.$data.moveWidget(i, 1)"
                                class="p-1.5 bg-white dark:bg-slate-700 rounded-lg shadow text-slate-400 hover:text-brand-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <button @click="$root.__x.$data.removeWidget(w.id)"
                                class="p-1.5 bg-white dark:bg-slate-700 rounded-lg shadow text-red-400 hover:text-red-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Edit outline --}}
                    <div x-show="$root.__x.$data.editMode"
                         class="absolute inset-0 rounded-2xl ring-2 ring-dashed ring-amber-300/60 dark:ring-amber-600/40 pointer-events-none z-0"></div>

                    {{-- Loading skeleton --}}
                    <div x-show="loading"
                         class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 animate-pulse">
                        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-1/3 mb-4"></div>
                        <div class="h-10 bg-slate-100 dark:bg-slate-800 rounded w-1/2 mb-2"></div>
                        <div class="h-3 bg-slate-100 dark:bg-slate-800 rounded w-2/3"></div>
                    </div>

                    {{-- Error state --}}
                    <div x-show="!loading && error"
                         class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-5">
                        <p class="text-xs font-semibold text-red-600 dark:text-red-400 mb-1" x-text="cfg.title || cfg.model"></p>
                        <p class="text-xs text-red-500 dark:text-red-300" x-text="error"></p>
                        <button @click="load()" class="mt-2 text-xs text-red-600 hover:underline">Reintentar</button>
                    </div>

                    {{-- STAT CARD --}}
                    <div x-show="!loading && !error && cfg.type === 'stat'"
                         class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-sm hover:shadow-md transition-shadow h-full">
                        <div class="flex items-center justify-between mb-5">
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400" x-text="cfg.title"></p>
                            <div class="p-2.5 rounded-xl"
                                 :class="{
                                     'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400':       cfg.color === 'blue',
                                     'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400': cfg.color === 'emerald',
                                     'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400':   cfg.color === 'amber',
                                     'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400':       cfg.color === 'rose',
                                     'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400': cfg.color === 'violet',
                                     'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400':           cfg.color === 'sky',
                                 }">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          :d="$root.__x.$data.available.find(a => a.model === cfg.model)?.icon || 'M4 6h16M4 10h16M4 14h16M4 18h16'"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-4xl font-black text-slate-900 dark:text-white tracking-tight leading-none"
                           x-text="data?.value ?? '-'"></p>
                        <p class="text-xs text-slate-400 mt-2 font-medium"
                           x-text="($root.__x.$data.available.find(a => a.model === cfg.model)?.label || cfg.model) + (cfg.operation !== 'count' ? ' - ' + cfg.operation : '')"></p>
                    </div>

                    {{-- RECENT TABLE --}}
                    <div x-show="!loading && !error && cfg.type === 'recent-table'"
                         class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden h-full">
                        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-700 dark:text-white" x-text="cfg.title"></p>
                            <span class="text-[10px] font-bold px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-full uppercase tracking-wider"
                                  x-text="(data?.records?.length || 0) + ' registros'"></span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-xs">
                                <thead class="bg-slate-50 dark:bg-slate-800/50">
                                    <tr>
                                        <template x-for="col in (data?.columns || [])" :key="col">
                                            <th class="px-4 py-2.5 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]" x-text="col"></th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                                    <template x-for="row in (data?.records || [])">
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                            <template x-for="col in (data?.columns || [])" :key="col">
                                                <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400 truncate max-w-[180px]"
                                                    x-text="row[col] ?? '-'"></td>
                                            </template>
                                        </tr>
                                    </template>
                                    <tr x-show="!data?.records?.length">
                                        <td :colspan="data?.columns?.length || 1" class="px-4 py-8 text-center text-slate-400 text-xs">Sin registros</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- CHARTS --}}
                    <div x-show="!loading && !error && isChart()"
                         class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 h-full">
                        <p class="text-sm font-semibold text-slate-700 dark:text-white mb-4" x-text="cfg.title"></p>
                        <div class="relative" style="height: 220px;">
                            <canvas></canvas>
                        </div>
                        <p x-show="data?.labels?.length === 0" class="text-xs text-center text-slate-400 mt-4">Sin datos disponibles</p>
                    </div>

                </div>
            </template>
        </div>

        {{-- ─── Add Widget Slide-Over ────────────────────────────────────── --}}
        <div x-show="addOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/40 z-40 backdrop-blur-sm"
             @click.self="addOpen = false">
        </div>

        <div x-show="addOpen"
             x-transition:enter="transition ease-out duration-250 transform"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="fixed top-0 right-0 h-full w-full max-w-md bg-white dark:bg-slate-900 shadow-2xl z-50 flex flex-col">

            {{-- Panel header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-x-3">
                    <div class="flex gap-x-1.5">
                        <span :class="step >= 1 ? 'bg-brand-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-400'"
                              class="w-6 h-6 rounded-full text-[10px] font-bold flex items-center justify-center">1</span>
                        <span :class="step >= 2 ? 'bg-brand-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-400'"
                              class="w-6 h-6 rounded-full text-[10px] font-bold flex items-center justify-center">2</span>
                    </div>
                    <p class="text-sm font-semibold text-slate-700 dark:text-white">
                        <span x-show="step === 1">Elegir tipo de widget</span>
                        <span x-show="step === 2">Configurar widget</span>
                    </p>
                </div>
                <button @click="addOpen = false" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Panel body --}}
            <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4">

                {{-- Step 1: Type picker --}}
                <div x-show="step === 1" class="grid grid-cols-1 gap-3">
                    @php
                    $types = [
                        ['key' => 'stat',         'label' => 'Tarjeta KPI',       'desc' => 'Numero unico: conteo, suma, promedio...', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                        ['key' => 'recent-table', 'label' => 'Tabla Reciente',     'desc' => 'Ultimos N registros de un modelo',        'icon' => 'M3 10h18M3 6h18M3 14h18M3 18h18'],
                        ['key' => 'line-chart',   'label' => 'Grafica de Lineas',  'desc' => 'Registros por fecha (created_at)',         'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
                        ['key' => 'bar-chart',    'label' => 'Grafica de Barras',  'desc' => 'Conteo agrupado por campo',               'icon' => 'M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['key' => 'pie-chart',    'label' => 'Grafica de Torta',   'desc' => 'Distribucion porcentual por campo',       'icon' => 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z'],
                    ];
                    @endphp

                    @foreach($types as $t)
                    <button type="button"
                            @click="pickType('{{ $t['key'] }}')"
                            class="flex items-center gap-x-4 p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 hover:border-brand-400 dark:hover:border-brand-500 hover:bg-brand-50/50 dark:hover:bg-brand-900/10 text-left transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center flex-shrink-0 group-hover:bg-brand-100 dark:group-hover:bg-brand-900/30 transition-colors">
                            <svg class="w-5 h-5 text-slate-500 dark:text-slate-400 group-hover:text-brand-600 dark:group-hover:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $t['icon'] }}"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-brand-700 dark:group-hover:text-brand-400">{{ $t['label'] }}</p>
                            <p class="text-xs text-slate-400">{{ $t['desc'] }}</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 ml-auto group-hover:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    @endforeach
                </div>

                {{-- Step 2: Configure --}}
                <div x-show="step === 2" class="space-y-5">

                    {{-- Model selector --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Fuente de datos</label>
                        <select x-model="nw.model" @change="nw.field = modelFields()[0] || null"
                                class="py-2.5 px-3 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
                            <template x-for="m in available" :key="m.model">
                                <option :value="m.model" x-text="m.label + ' (' + m.model + ')'"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Stat: operation + field --}}
                    <template x-if="nw.type === 'stat'">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Operacion</label>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="op in ['count','sum','avg','max','min']" :key="op">
                                        <button type="button" @click="nw.operation = op"
                                                :class="nw.operation === op ? 'bg-brand-600 text-white border-brand-600' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:border-brand-400'"
                                                class="px-4 py-2 rounded-lg border text-xs font-semibold transition-all" x-text="op.toUpperCase()"></button>
                                    </template>
                                </div>
                            </div>
                            <div x-show="nw.operation !== 'count'">
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Campo numerico</label>
                                <select x-model="nw.field"
                                        class="py-2.5 px-3 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
                                    <template x-for="f in modelFields()" :key="f">
                                        <option :value="f" x-text="f"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Color</label>
                                <div class="flex items-center gap-x-2.5">
                                    <template x-for="c in ['blue','emerald','amber','rose','violet','sky']" :key="c">
                                        <button type="button" @click="nw.color = c"
                                                :class="{
                                                    'scale-125 ring-2 ring-offset-2 dark:ring-offset-slate-900': nw.color === c,
                                                    'ring-blue-500 bg-blue-500':       c === 'blue',
                                                    'ring-emerald-500 bg-emerald-500': c === 'emerald',
                                                    'ring-amber-500 bg-amber-500':     c === 'amber',
                                                    'ring-rose-500 bg-rose-500':       c === 'rose',
                                                    'ring-violet-500 bg-violet-500':   c === 'violet',
                                                    'ring-sky-500 bg-sky-500':         c === 'sky',
                                                }"
                                                class="w-7 h-7 rounded-full transition-all border-2 border-white dark:border-slate-800"></button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Table: limit --}}
                    <template x-if="nw.type === 'recent-table'">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Maximo de filas</label>
                            <select x-model.number="nw.limit"
                                    class="py-2.5 px-3 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
                                <option value="5">5 registros</option>
                                <option value="10">10 registros</option>
                                <option value="15">15 registros</option>
                                <option value="20">20 registros</option>
                            </select>
                        </div>
                    </template>

                    {{-- Bar / Pie: group by field --}}
                    <template x-if="nw.type === 'bar-chart' || nw.type === 'pie-chart'">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Agrupar por campo</label>
                            <select x-model="nw.field"
                                    class="py-2.5 px-3 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
                                <template x-for="f in modelFields()" :key="f">
                                    <option :value="f" x-text="f"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    {{-- Line chart info --}}
                    <template x-if="nw.type === 'line-chart'">
                        <div class="flex items-start gap-x-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-3">
                            <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-xs text-blue-700 dark:text-blue-300">Muestra registros creados por dia usando la columna <code class="font-mono bg-blue-100 dark:bg-blue-900/40 px-1 rounded">created_at</code>.</p>
                        </div>
                    </template>

                    {{-- Title input --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Titulo <span class="font-normal opacity-60">(opcional)</span></label>
                        <input type="text" x-model="nw.title"
                               placeholder="Se genera automaticamente"
                               class="py-2.5 px-3 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
                    </div>

                    {{-- Size picker --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Tamano</label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach([1 => '1/4', 2 => '1/2', 3 => '3/4', 4 => 'Full'] as $s => $label)
                            <button type="button" @click="nw.size = {{ $s }}"
                                    :class="nw.size == {{ $s }} ? 'bg-brand-600 text-white border-brand-600' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:border-brand-400'"
                                    class="py-2 rounded-lg border text-xs font-semibold transition-all">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>

            {{-- Panel footer --}}
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-x-3">
                <button @click="step === 1 ? (addOpen = false) : (step = 1)"
                        class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors">
                    <span x-show="step === 1">Cancelar</span>
                    <span x-show="step === 2">&#8592; Atras</span>
                </button>
                <button x-show="step === 2" @click="addWidget()"
                        class="flex-1 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm">
                    Agregar al dashboard
                </button>
            </div>
        </div>

    </div>
</x-app-layout>
