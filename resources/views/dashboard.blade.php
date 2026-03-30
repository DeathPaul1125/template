<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    <div class="space-y-8 animate-in">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $stats = [
                    ['label' => 'Usuarios Totales', 'value' => \App\Models\User::count(), 'icon' => 'users', 'color' => 'blue', 'change' => '+12%'],
                    ['label' => 'Roles Activos',    'value' => \Spatie\Permission\Models\Role::count(), 'icon' => 'shield', 'color' => 'indigo', 'change' => 'Configurados'],
                    ['label' => 'Permisos',          'value' => \Spatie\Permission\Models\Permission::count(), 'icon' => 'key', 'color' => 'sky', 'change' => 'Activos'],
                    ['label' => 'Sesión',            'value' => 'Activa', 'icon' => 'check', 'color' => 'emerald', 'change' => 'En línea'],
                ];
            @endphp

            @foreach($stats as $stat)
            <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-2xl 
                        @if($stat['color'] === 'blue') bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 
                        @elseif($stat['color'] === 'indigo') bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400
                        @elseif($stat['color'] === 'sky') bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400
                        @else bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 @endif
                        shadow-sm border border-white/20">
                        @if($stat['icon'] === 'users')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        @elseif($stat['icon'] === 'shield')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        @elseif($stat['icon'] === 'key')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        @else
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="text-3xl font-black text-slate-900 dark:text-white tracking-tight leading-none">
                        {{ $stat['value'] }}
                    </p>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                        {{ $stat['label'] }}
                    </p>
                </div>
                <div class="mt-4 pt-4 border-t border-slate-50 dark:border-slate-800 flex items-center justify-between">
                    <span class="text-[10px] font-black uppercase tracking-tighter text-slate-400">Desde último log</span>
                    <span class="text-[10px] font-black px-2 py-1 rounded-lg 
                        @if($stat['color'] === 'blue') bg-blue-100 text-blue-700 
                        @elseif($stat['color'] === 'indigo') bg-indigo-100 text-indigo-700
                        @else bg-emerald-100 text-emerald-700 @endif">
                        {{ $stat['change'] }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>

        <!-- System Status & Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content Card -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm p-8 md:p-10 relative overflow-hidden group">
                <!-- Decorative element -->
                <div class="absolute -top-12 -right-12 w-48 h-48 bg-blue-600/5 rounded-full blur-3xl group-hover:bg-blue-600/10 transition-all duration-700"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center gap-x-4 mb-10">
                        <div class="w-16 h-16 bg-blue-600 rounded-[1.25rem] flex items-center justify-center text-white text-2xl font-black shadow-xl shadow-blue-500/20">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 font-bold uppercase tracking-widest">Estado del Operador</p>
                            <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                                {{ Auth::user()->name }}
                            </h2>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                        <div class="space-y-4">
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Resumen de Acceso</h3>
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/50 border border-slate-100 dark:border-slate-800">
                                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Rol Asignado</p>
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                    {{ Auth::user()->getRoleNames()->first() ?? 'Sin permisos asignados' }}
                                </p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Actividad</h3>
                            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Sesión Iniciada</p>
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                    En línea ahora
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('profile.show') }}" class="px-8 py-3 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-black rounded-2xl shadow-lg transition-all hover:scale-[1.03] active:scale-95 uppercase tracking-widest">
                            Configurar Perfil
                        </a>
                        @can('manage-users')
                        <a href="{{ route('users.index') }}" class="px-8 py-3 bg-blue-600 text-white text-xs font-black rounded-2xl shadow-lg shadow-blue-500/20 transition-all hover:scale-[1.03] active:scale-95 uppercase tracking-widest">
                            Gestionar Usuarios
                        </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Sidemenu Info -->
            <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 border border-slate-100 dark:border-slate-800 shadow-sm group">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-8 px-2">Detalles Técnicos</h3>
                <div class="space-y-6">
                    <div class="flex items-start gap-x-4 border-b border-slate-50 dark:border-slate-800 pb-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-950 flex items-center justify-center text-slate-500 group-hover:text-blue-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] text-slate-400 font-black uppercase tracking-tighter">Email Corporativo</p>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-300 truncate">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-x-4 border-b border-slate-50 dark:border-slate-800 pb-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-950 flex items-center justify-center text-slate-500 group-hover:text-blue-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] text-slate-400 font-black uppercase tracking-tighter">Miembro Desde</p>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-300">{{ Auth::user()->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-x-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-950 flex items-center justify-center text-slate-500 group-hover:text-blue-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] text-slate-400 font-black uppercase tracking-tighter">Permisos del Sistema</p>
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-300">{{ Auth::user()->getPermissionNames()->count() }} Activos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
