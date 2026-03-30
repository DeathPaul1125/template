<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @php
            $stats = [
                ['label' => 'Usuarios Totales', 'value' => \App\Models\User::count(), 'icon' => 'users', 'color' => 'brand', 'change' => '+12%'],
                ['label' => 'Roles Activos',    'value' => \Spatie\Permission\Models\Role::count(), 'icon' => 'shield', 'color' => 'violet', 'change' => '+2'],
                ['label' => 'Permisos',          'value' => \Spatie\Permission\Models\Permission::count(), 'icon' => 'key', 'color' => 'amber', 'change' => 'Activos'],
                ['label' => 'Sesión Activa',     'value' => '1', 'icon' => 'check', 'color' => 'teal', 'change' => 'Ahora'],
            ];
        @endphp

        @foreach($stats as $stat)
        <div class="premium-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-500">{{ $stat['label'] }}</p>
                    <p class="mt-3 text-4xl font-extrabold text-slate-800 dark:text-white tracking-tight">{{ $stat['value'] }}</p>
                    <div class="mt-4 flex items-center gap-x-1.5 px-2.5 py-1 rounded-full text-xs font-bold w-fit
                        @if($stat['color'] === 'brand') bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400
                        @elseif($stat['color'] === 'violet') bg-violet-50 text-violet-700 dark:bg-violet-500/10 dark:text-violet-400
                        @elseif($stat['color'] === 'amber') bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400
                        @else bg-teal-50 text-teal-700 dark:bg-teal-500/10 dark:text-teal-400 @endif">
                        {{ $stat['change'] }}
                    </div>
                </div>
                <div class="p-3.5 rounded-2xl shadow-sm border border-white/20
                    @if($stat['color'] === 'brand') bg-brand-600 text-white shadow-brand-500/20
                    @elseif($stat['color'] === 'violet') bg-violet-600 text-white shadow-violet-500/20
                    @elseif($stat['color'] === 'amber') bg-amber-600 text-white shadow-amber-500/20
                    @else bg-teal-600 text-white shadow-teal-500/20 @endif">
                    @if($stat['icon'] === 'users')
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    @elseif($stat['icon'] === 'shield')
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    @elseif($stat['icon'] === 'key')
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    @else
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Welcome + Quick actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Welcome card -->
        <div class="lg:col-span-2 bg-gradient-to-br from-brand-600 to-brand-800 rounded-2xl p-6 text-white relative overflow-hidden shadow-lg">
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/5 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-brand-400/10 rounded-full blur-2xl"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-x-3 mb-4">
                    <div class="w-12 h-12 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center text-2xl font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-brand-200 text-sm">Bienvenido de vuelta</p>
                        <h3 class="text-xl font-bold">{{ Auth::user()->name }}</h3>
                    </div>
                </div>
                <p class="text-brand-200 text-sm mb-5">
                    Tienes acceso como <strong class="text-white">{{ Auth::user()->getRoleNames()->first() ?? 'Usuario' }}</strong>.
                    Usa el menú lateral para navegar por las secciones disponibles.
                </p>
                <div class="flex gap-3 flex-wrap">
                    <a href="{{ route('profile.show') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 backdrop-blur-sm text-white text-sm font-medium rounded-xl border border-white/20 transition-all">
                        Ver perfil
                    </a>
                    @can('manage-users')
                    <a href="{{ route('users.index') }}" class="px-4 py-2 bg-white text-brand-700 hover:bg-brand-50 text-sm font-medium rounded-xl transition-all shadow-sm">
                        Gestionar Usuarios
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Quick info -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-gray-200 dark:border-slate-700 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-white mb-4">Información de Cuenta</h3>
            <div class="space-y-3">
                <div class="flex items-center gap-x-3 py-2 border-b border-gray-100 dark:border-slate-700">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-400">Email</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300 truncate">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-x-3 py-2 border-b border-gray-100 dark:border-slate-700">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <div>
                        <p class="text-xs text-slate-400">Rol</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ Auth::user()->getRoleNames()->first() ?? 'Sin rol' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-x-3 py-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <div>
                        <p class="text-xs text-slate-400">Miembro desde</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ Auth::user()->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
