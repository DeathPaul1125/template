<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
    window.__dashLayout = @json($layout);
    window.__dashModels = @json($models);
    </script>
    <script>
    // â”€â”€ Dashboard builder â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function dashboard(saved, available) {
        return {
            widgets:   saved,
            available: available,
            editMode:  false,
            addOpen:   false,
            saving:    false,
            savedOk:   false,
            editingId: null,

            step: 1,
            nw:   {},

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
                this.step      = 1;
                this.editingId = null;
            },

            currentModel() {
                return this.available.find(a => a.model === this.nw.model) || null;
            },

            modelFields() {
                // Returns [{name, type}] objects
                return this.currentModel()?.fields || [];
            },

            numericFields() {
                const numTypes = ['integer','bigInteger','decimal','float','numeric','int','double'];
                return this.modelFields().filter(f => numTypes.includes(f.type));
            },

            hasTimestamps() {
                return this.currentModel()?.hasTimestamps ?? false;
            },

            isChart() {
                return ['bar-chart','pie-chart','line-chart'].includes(this.nw.type);
            },

            suggestSize() {
                const map = { stat: 1, 'growth-stat': 1, 'recent-table': 4, 'line-chart': 4, 'bar-chart': 2, 'pie-chart': 2, 'top-list': 2 };
                this.nw.size = map[this.nw.type] || 2;
            },

            pickType(t) {
                this.nw.type  = t;
                this.suggestSize();
                this.nw.field = this.modelFields()[0]?.name || null;
                this.step = 2;
            },

            colClass(size) {
                const map = { 1: 'col-span-4 lg:col-span-1', 2: 'col-span-4 sm:col-span-2', 3: 'col-span-4 sm:col-span-2 lg:col-span-3', 4: 'col-span-4' };
                return map[size] || map[2];
            },

            openAdd() { this.resetNw(); this.addOpen = true; },

            openEdit(id) {
                const w = this.widgets.find(x => x.id === id);
                if (!w) return;
                this.nw        = { ...w };
                this.editingId = id;
                this.step      = 2;
                this.addOpen   = true;
            },

            addWidget() {
                if (!this.nw.title.trim()) {
                    const m = this.currentModel();
                    const lbl = { stat: 'Tarjeta', 'growth-stat': 'Tendencia', 'recent-table': 'Tabla', 'line-chart': 'Li­nea', 'bar-chart': 'Barras', 'pie-chart': 'Dona', 'top-list': 'Top N' };
                    this.nw.title = (m?.label || this.nw.model) + ' ' + (lbl[this.nw.type] || this.nw.type);
                }
                if (this.editingId) {
                    const idx = this.widgets.findIndex(w => w.id === this.editingId);
                    if (idx !== -1) this.widgets.splice(idx, 1, { ...this.nw, id: this.editingId });
                    this.editingId = null;
                } else {
                    this.widgets.push({ ...this.nw });
                }
                this.addOpen = false;
                this.save();
            },

            removeWidget(id) { this.widgets = this.widgets.filter(w => w.id !== id); this.save(); },

            moveWidget(index, dir) {
                const t = index + dir;
                if (t < 0 || t >= this.widgets.length) return;
                [this.widgets[index], this.widgets[t]] = [this.widgets[t], this.widgets[index]];
                this.save();
            },

            async save() {
                this.saving = true;
                try {
                    await fetch('/dashboard/save-layout', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ widgets: this.widgets })
                    });
                    this.savedOk = true;
                    setTimeout(() => this.savedOk = false, 2500);
                } finally { this.saving = false; }
            }
        };
    }

    // â”€â”€ Individual widget component â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function widgetComp(cfg) {
        return {
            cfg:     cfg,
            data:    null,
            loading: true,
            error:   null,
            chart:   null,

            isChart() { return ['bar-chart','pie-chart','line-chart'].includes(this.cfg.type); },

            async init()  { await this.load(); },

            async load() {
                this.loading = true;
                this.error   = null;
                try {
                    const res  = await fetch('/dashboard/widget-data', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify(this.cfg)
                    });
                    const json = await res.json();
                    if (json.error) {
                        this.error = json.error;
                    } else {
                        this.data = json;
                        if (this.isChart()) { await this.$nextTick(); this.initChart(); }
                    }
                } catch (e) { this.error = 'Error de red.'; }
                finally     { this.loading = false; }
            },

            initChart() {
                const canvas = this.$el.querySelector('canvas');
                if (!canvas || !this.data) return;
                if (this.chart) this.chart.destroy();

                const palette   = ['#0e8ceb','#026ec7','#38a9f8','#7cc8fb','#10b981','#059669','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16'];
                const isDark    = document.documentElement.classList.contains('dark');
                const gridColor = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.06)';
                const textColor = isDark ? '#94a3b8' : '#64748b';
                const type      = this.cfg.type === 'bar-chart' ? 'bar' : this.cfg.type === 'pie-chart' ? 'doughnut' : 'line';
                const isDonut   = type === 'doughnut';

                this.chart = new Chart(canvas, {
                    type,
                    data: {
                        labels: this.data.labels,
                        datasets: [{
                            label:           this.cfg.title,
                            data:            this.data.values,
                            backgroundColor: isDonut ? palette : palette[0] + '33',
                            borderColor:     isDonut ? palette.map(c => c + 'cc') : palette[0],
                            borderWidth: 2, tension: 0.4, fill: type === 'line',
                            pointRadius: type === 'line' ? 3 : 0, pointHoverRadius: type === 'line' ? 5 : 0,
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: isDonut, position: 'bottom', labels: { color: textColor, padding: 12, font: { size: 11 } } } },
                        scales: !isDonut ? {
                            x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } },
                            y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } }
                        } : undefined,
                    }
                });
            },

            renderCell(col, value, typeMap) {
                if (value === null || value === undefined) return 'â€”';
                const type = (typeMap || {})[col] || 'string';
                if (type === 'boolean') return value ? 'SÃ­' : 'No';
                if (type === 'image' || type === 'file') return value ? '[ archivo ]' : 'â€”';
                if (type === 'date' || col === 'created_at' || col === 'updated_at' || col === 'deleted_at') {
                    try { return new Date(value).toLocaleDateString('es', { day: '2-digit', month: 'short', year: 'numeric' }); } catch(e) {}
                }
                const s = String(value);
                return s.length > 48 ? s.substring(0, 48) + 'â€¦' : s;
            },

            isBool(col, typeMap) { return (typeMap || {})[col] === 'boolean'; },

            colorClass(color) {
                const m = {
                    blue:    'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
                    emerald: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
                    amber:   'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
                    rose:    'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
                    violet:  'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400',
                    sky:     'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
                };
                return m[color] || m.blue;
            },
        };
    }
    </script>
    @endpush

    {{-- Tailwind JIT safelist hint --}}
    {{-- col-span-1 col-span-2 col-span-3 col-span-4 sm:col-span-2 lg:col-span-1 lg:col-span-2 lg:col-span-3 lg:col-span-4 bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400 bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400 bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400 bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400 ring-blue-500 ring-emerald-500 ring-amber-500 ring-rose-500 ring-violet-500 ring-sky-500 bg-blue-500 bg-emerald-500 bg-amber-500 bg-rose-500 bg-violet-500 bg-sky-500 --}}

    <div x-data="dashboard(window.__dashLayout, window.__dashModels)">

        {{-- â”€â”€ Header â”€â”€ --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-800 dark:text-white">Dashboard</h1>
                <p class="text-xs text-slate-400 mt-0.5" x-show="editMode">Modo de edicion activo arrastra los widgets o usa las flechas para reorganizarlos.</p>
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

        {{-- â”€â”€ Empty state â”€â”€ --}}
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

        {{-- â”€â”€ Widget grid â”€â”€ --}}
        <div x-show="widgets.length > 0" class="grid grid-cols-4 gap-5">
            <template x-for="(w, i) in widgets" :key="w.id">
                <div :class="colClass(w.size)"
                     x-data="widgetComp(w)"
                     class="relative group">

                    {{-- Edit controls (visible in edit mode on hover) --}}
                    <div x-show="editMode"
                         class="absolute top-2 right-2 z-10 flex items-center gap-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="load()" title="Actualizar"
                                class="p-1.5 bg-white dark:bg-slate-700 rounded-lg shadow text-slate-400 hover:text-sky-600 dark:hover:text-sky-400 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                        <button @click="openEdit(w.id)" title="Editar"
                                class="p-1.5 bg-white dark:bg-slate-700 rounded-lg shadow text-slate-400 hover:text-brand-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button @click="moveWidget(i, -1)" title="Mover izquierda"
                                class="p-1.5 bg-white dark:bg-slate-700 rounded-lg shadow text-slate-400 hover:text-brand-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        </button>
                        <button @click="moveWidget(i, 1)" title="Mover derecha"
                                class="p-1.5 bg-white dark:bg-slate-700 rounded-lg shadow text-slate-400 hover:text-brand-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <button @click="removeWidget(w.id)" title="Eliminar"
                                class="p-1.5 bg-white dark:bg-slate-700 rounded-lg shadow text-red-400 hover:text-red-600 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Edit outline --}}
                    <div x-show="editMode"
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

                    {{-- â•â•â• STAT CARD â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
                    <div x-show="!loading && !error && cfg.type === 'stat'"
                         class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-sm hover:shadow-md transition-shadow h-full">
                        <div class="flex items-center justify-between mb-5">
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400" x-text="cfg.title"></p>
                            <div class="p-2.5 rounded-xl" :class="colorClass(cfg.color)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="available.find(a => a.model === cfg.model)?.icon || 'M4 6h16M4 10h16M4 14h16M4 18h16'"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-4xl font-black text-slate-900 dark:text-white tracking-tight leading-none" x-text="data?.value ?? '-'"></p>
                        <p class="text-xs text-slate-400 mt-2 font-medium"
                           x-text="(available.find(a => a.model === cfg.model)?.label || cfg.model) + (cfg.operation !== 'count' ? ' â€” ' + cfg.operation : '')"></p>
                    </div>

                    {{-- â•â•â• GROWTH STAT CARD â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
                    <div x-show="!loading && !error && cfg.type === 'growth-stat'"
                         class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 p-6 shadow-sm hover:shadow-md transition-shadow h-full">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400" x-text="cfg.title"></p>
                            <div class="p-2.5 rounded-xl" :class="colorClass(cfg.color)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="available.find(a => a.model === cfg.model)?.icon || 'M4 6h16M4 10h16M4 14h16M4 18h16'"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-4xl font-black text-slate-900 dark:text-white tracking-tight leading-none mb-3" x-text="data?.value ?? '-'"></p>
                        <div class="flex items-center gap-x-2 flex-wrap">
                            <template x-if="data?.trend === 'up'">
                                <span class="inline-flex items-center gap-x-0.5 px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full text-[11px] font-bold">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7 7 7"/></svg>
                                    <span x-text="'+' + data.change + '%'"></span>
                                </span>
                            </template>
                            <template x-if="data?.trend === 'down'">
                                <span class="inline-flex items-center gap-x-0.5 px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-full text-[11px] font-bold">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7-7-7"/></svg>
                                    <span x-text="data.change + '%'"></span>
                                </span>
                            </template>
                            <p class="text-[11px] text-slate-400">
                                <span x-text="(data?.current ?? 'â€”') + ' este mes'"></span>
                                <template x-if="data?.previous !== null && data?.previous !== undefined">
                                    <span x-text="' Â· ' + data.previous + ' mes anterior'"></span>
                                </template>
                            </p>
                        </div>
                    </div>

                    {{-- â•â•â• RECENT TABLE â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
                    <div x-show="!loading && !error && cfg.type === 'recent-table'"
                         class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden h-full">
                        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-700 dark:text-white" x-text="cfg.title"></p>
                            <div class="flex items-center gap-x-2">
                                <span class="text-[10px] font-bold px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-full uppercase tracking-wider"
                                      x-text="(data?.records?.length || 0) + ' reg.'"></span>
                                <template x-if="available.find(a => a.model === cfg.model)?.routeBase">
                                    <a :href="'/' + available.find(a => a.model === cfg.model)?.routeBase"
                                       class="text-[10px] font-semibold text-brand-600 hover:underline">Ver todos</a>
                                </template>
                            </div>
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
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                            <template x-for="col in (data?.columns || [])" :key="col">
                                                <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400">
                                                    <template x-if="isBool(col, data?.typeMap)">
                                                        <span :class="row[col] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'"
                                                              class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                                                              x-text="row[col] ? 'SÃ­' : 'No'"></span>
                                                    </template>
                                                    <template x-if="!isBool(col, data?.typeMap)">
                                                        <span class="block truncate max-w-[160px]" x-text="renderCell(col, row[col], data?.typeMap)"></span>
                                                    </template>
                                                </td>
                                            </template>
                                        </tr>
                                    </template>
                                    <tr x-show="!data?.records?.length">
                                        <td :colspan="data?.columns?.length || 1" class="px-4 py-8 text-center text-slate-400">Sin registros</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- â•â•â• CHARTS (bar, pie, line) â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
                    <div x-show="!loading && !error && isChart()"
                         class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 h-full">
                        <p class="text-sm font-semibold text-slate-700 dark:text-white mb-4" x-text="cfg.title"></p>
                        <div class="relative" style="height: 220px;">
                            <canvas></canvas>
                        </div>
                        <p x-show="data?.labels?.length === 0" class="text-xs text-center text-slate-400 mt-4">Sin datos disponibles</p>
                    </div>

                    {{-- â•â•â• TOP LIST â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
                    <div x-show="!loading && !error && cfg.type === 'top-list'"
                         class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-5 h-full">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-sm font-semibold text-slate-700 dark:text-white" x-text="cfg.title"></p>
                            <span class="text-[10px] font-bold px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 rounded-full uppercase tracking-wider" x-text="data?.field || cfg.field"></span>
                        </div>
                        <div class="space-y-3">
                            <template x-for="(item, idx) in (data?.items || [])">
                                <div class="flex items-center gap-x-3">
                                    <span class="text-[10px] font-bold text-slate-400 w-5 text-right shrink-0" x-text="idx + 1"></span>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-xs text-slate-700 dark:text-slate-300 truncate" x-text="item.label"></span>
                                            <span class="text-xs font-bold text-slate-600 dark:text-slate-400 ml-2 tabular-nums" x-text="Number(item.value).toLocaleString('es')"></span>
                                        </div>
                                        <div class="h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full bg-brand-600/70 transition-all duration-500"
                                                 :style="'width:' + Math.round((item.value / (data?.max || 1)) * 100) + '%'"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <p x-show="!data?.items?.length" class="text-xs text-center text-slate-400 py-4">Sin datos</p>
                        </div>
                    </div>

                </div>
            </template>
        </div>

        {{-- â”€â”€â”€ Add / Edit Widget Slide-Over â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
        <template x-teleport="body">
        <div x-show="addOpen"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/40 z-40 backdrop-blur-sm" @click.self="addOpen = false">
        </div>
        </template>

        <template x-teleport="body">
        <div x-show="addOpen"
             x-transition:enter="transition ease-out duration-250 transform" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"  x-transition:leave-start="translate-x-0"    x-transition:leave-end="translate-x-full"
             class="fixed top-0 right-0 h-full w-full max-w-md bg-white dark:bg-slate-900 shadow-2xl z-50 flex flex-col">

            {{-- Panel header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-x-3">
                    <div x-show="!editingId" class="flex gap-x-1.5">
                        <span :class="step >= 1 ? 'bg-brand-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-400'"
                              class="w-6 h-6 rounded-full text-[10px] font-bold flex items-center justify-center">1</span>
                        <span :class="step >= 2 ? 'bg-brand-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-400'"
                              class="w-6 h-6 rounded-full text-[10px] font-bold flex items-center justify-center">2</span>
                    </div>
                    <p class="text-sm font-semibold text-slate-700 dark:text-white">
                        <span x-show="editingId">Editar widget</span>
                        <span x-show="!editingId && step === 1">Elegir tipo de widget</span>
                        <span x-show="!editingId && step === 2">Configurar widget</span>
                    </p>
                </div>
                <button @click="addOpen = false; editingId = null" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Panel body --}}
            <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4">

                {{-- Step 1: Type picker --}}
                <div x-show="step === 1" class="grid grid-cols-1 gap-3">
                    @php
                    $types = [
                        ['key' => 'stat',         'label' => 'Tarjeta KPI',        'desc' => 'Numero unico: conteo, suma, promedio...',          'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                        ['key' => 'growth-stat',  'label' => 'Tarjeta Tendencia',   'desc' => 'Total con % de cambio vs. mes anterior',         'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
                        ['key' => 'recent-table', 'label' => 'Tabla Reciente',      'desc' => 'Ultimos N registros de un modelo',               'icon' => 'M3 10h18M3 6h18M3 14h18M3 18h18'],
                        ['key' => 'top-list',     'label' => 'Lista Top N',         'desc' => 'Ranking de registros por campo numerico',        'icon' => 'M4 6h16M4 10h10M4 14h6M4 18h3'],
                        ['key' => 'line-chart',   'label' => 'Grafica de Lineas',   'desc' => 'Registros por fecha (ultimos 60 dias)',          'icon' => 'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h13M3 8h9m-9 4h6'],
                        ['key' => 'bar-chart',    'label' => 'Grafica de Barras',   'desc' => 'Conteo agrupado por campo',                     'icon' => 'M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['key' => 'pie-chart',    'label' => 'Grafica de Dona',     'desc' => 'Distribucion porcentual por campo',             'icon' => 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z'],
                    ];
                    @endphp

                    @foreach($types as $t)
                    <button type="button" @click="pickType('{{ $t['key'] }}')"
                            class="flex items-center gap-x-4 p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 hover:border-brand-400 dark:hover:border-brand-500 hover:bg-brand-50/50 dark:hover:bg-brand-900/10 text-left transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center flex-shrink-0 group-hover:bg-brand-100 dark:group-hover:bg-brand-900/30 transition-colors">
                            <svg class="w-5 h-5 text-slate-500 dark:text-slate-400 group-hover:text-brand-600 dark:group-hover:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $t['icon'] }}"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-brand-700 dark:group-hover:text-brand-400">{{ $t['label'] }}</p>
                            <p class="text-xs text-slate-400">{{ $t['desc'] }}</p>
                        </div>
                        <svg class="w-4 h-4 text-slate-300 group-hover:text-brand-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    @endforeach
                </div>

                {{-- Step 2: Configure --}}
                <div x-show="step === 2" class="space-y-5">

                    {{-- Model selector --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Fuente de datos</label>
                        <select x-model="nw.model" @change="nw.field = modelFields()[0]?.name || null"
                                class="py-2.5 px-3 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
                            <template x-for="m in available" :key="m.model">
                                <option :value="m.model" x-text="m.label + ' (' + m.model + ')'"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Stat / Growth-stat: operation + field --}}
                    <template x-if="nw.type === 'stat' || nw.type === 'growth-stat'">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">OperaciÃ³n</label>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="op in ['count','sum','avg','max','min']" :key="op">
                                        <button type="button" @click="nw.operation = op"
                                                :class="nw.operation === op ? 'bg-brand-600 text-white border-brand-600' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:border-brand-400'"
                                                class="px-4 py-2 rounded-lg border text-xs font-semibold transition-all" x-text="op.toUpperCase()"></button>
                                    </template>
                                </div>
                            </div>
                            <div x-show="nw.operation !== 'count'">
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Campo numÃ©rico</label>
                                <select x-model="nw.field"
                                        class="py-2.5 px-3 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
                                    <template x-for="f in modelFields()" :key="f.name">
                                        <option :value="f.name" x-text="f.name"></option>
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

                    {{-- Recent table: limit --}}
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

                    {{-- Top list: field + limit --}}
                    <template x-if="nw.type === 'top-list'">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Campo de medida (numerico)</label>
                                <select x-model="nw.field"
                                        class="py-2.5 px-3 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
                                    <template x-for="f in modelFields()" :key="f.name">
                                        <option :value="f.name" x-text="f.name + (f.type !== 'string' ? '  (' + f.type + ')' : '')"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Maximo de items</label>
                                <select x-model.number="nw.limit"
                                        class="py-2.5 px-3 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
                                    <option value="5">Top 5</option>
                                    <option value="10">Top 10</option>
                                </select>
                            </div>
                        </div>
                    </template>

                    {{-- Bar / Pie: group by field --}}
                    <template x-if="nw.type === 'bar-chart' || nw.type === 'pie-chart'">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Agrupar por campo</label>
                            <select x-model="nw.field"
                                    class="py-2.5 px-3 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
                                <template x-for="f in modelFields()" :key="f.name">
                                    <option :value="f.name" x-text="f.name"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    {{-- Line chart info --}}
                    <template x-if="nw.type === 'line-chart'">
                        <div class="flex items-start gap-x-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl p-3">
                            <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-xs text-blue-700 dark:text-blue-300">Muestra registros creados por día usando la columna <code class="font-mono bg-blue-100 dark:bg-blue-900/40 px-1 rounded">created_at</code> (últimos 60 días).</p>
                        </div>
                    </template>

                    {{-- Title --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Título <span class="font-normal opacity-60">(opcional)</span></label>
                        <input type="text" x-model="nw.title" placeholder="Se genera automáticamente"
                               class="py-2.5 px-3 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300">
                    </div>

                    {{-- Size picker --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Tamaño</label>
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
                <button @click="editingId ? (addOpen = false) : (step === 1 ? (addOpen = false) : (step = 1))"
                        class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors">
                    <span x-show="editingId || step === 1">Cancelar</span>
                    <span x-show="!editingId && step === 2">← Atrás</span>
                </button>
                <button x-show="step === 2" @click="addWidget()"
                        class="flex-1 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm"
                        x-text="editingId ? 'Guardar cambios' : 'Agregar al dashboard'">
                </button>
            </div>
        </div>
        </template>

    </div>
</x-app-layout>

