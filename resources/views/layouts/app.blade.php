<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \App\Models\Setting::get('site_name', config('app.name', 'Laravel')) }} — {{ $title ?? 'Panel' }}</title>

    @if($favicon = \App\Models\Setting::get('site_favicon'))
        <link rel="icon" type="image/x-icon" href="{{ $favicon }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- dynamic brand color -->
    @php
        $brandColor = \App\Models\Setting::get('brand_color', '#0e8ceb');

        // Convert hex to rgb for tailwind shadows
        list($r, $g, $b) = sscanf($brandColor, "#%02x%02x%02x");
        $brandColorRgb = "$r, $g, $b";
    @endphp
    <style>
        :root {
            --brand-color: {{ $brandColor }};
            --brand-color-rgb: {{ $brandColorRgb }};
            --brand-color-hover: {{ $brandColor }}dd;
        }
        .bg-brand-600 { background-color: var(--brand-color) !important; }
        .hover\:bg-brand-700:hover { background-color: var(--brand-color) !important; filter: brightness(0.9); }
        .text-brand-600 { color: var(--brand-color) !important; }
        .border-brand-500 { border-color: var(--brand-color) !important; }
        .focus\:border-brand-500:focus { border-color: var(--brand-color) !important; }
        .from-brand-600 { --tw-gradient-from: var(--brand-color) !important; }
        .to-brand-800 { --tw-gradient-to: var(--brand-color) !important; filter: brightness(0.8); }

        /* Elite Blue Sidebar Active State */
        .sidebar-link.active {
            @apply bg-blue-600/10 text-blue-400 border border-blue-500/20 shadow-[0_0_15px_-5px_rgba(14,140,235,0.4)];
        }
    </style>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.min.css">
</head>
<body class="bg-gray-50 dark:bg-slate-900 h-full font-sans antialiased">

{{-- ===== SIDEBAR ===== --}}
<div id="hs-application-sidebar"
     class="hs-overlay [--auto-close:lg] hs-overlay-open:translate-x-0 -translate-x-full transition-all duration-500 transform hidden fixed top-0 start-0 bottom-0 z-[60] w-64 bg-slate-950 border-e border-white/5 overflow-y-auto lg:block lg:translate-x-0 lg:end-auto lg:bottom-0 [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-slate-950 [&::-webkit-scrollbar-thumb]:bg-slate-800"
     style="background-color: #020617 !important;"
     aria-label="Sidebar">

    <nav class="w-64 flex flex-col h-full bg-transparent">
        <!-- Logo / Brand -->
        <div class="px-6 pt-6 pb-4">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-x-2">
                @if($logo = \App\Models\Setting::get('site_logo'))
                    <img src="{{ $logo }}" alt="Logo" class="w-8 h-8 object-contain">
                @else
                    <div class="w-8 h-8 bg-gradient-to-br from-brand-500 to-brand-700 rounded-lg flex items-center justify-center shadow-md">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                @endif
                <span class="text-xl font-bold text-white tracking-tight">
                {{ \App\Models\Setting::get('site_name', config('app.name', 'Laravel')) }}
            </span>
        </div>

        <div class="h-full px-4 pb-4 space-y-1.5 [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-slate-800 [&::-webkit-scrollbar-thumb]:bg-slate-600 overflow-y-auto">
            <div class="py-2 mb-2 border-b border-white/10">
                <p class="px-3 text-[11px] font-bold uppercase tracking-widest text-slate-400">Menú Principal</p>
            </div>

            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}"
               class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>

            <!-- Administración -->
            @canany(['manage-users', 'manage-roles'])
            <div class="pt-5 pb-2">
                <span class="px-3 text-[10px] font-bold uppercase tracking-[0.15em] text-white/50">
                    Administración
                </span>
            </div>
            @endcanany

            @can('manage-users')
            <!-- Usuarios -->
            <a href="{{ route('users.index') }}"
               class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Usuarios
            </a>
            @endcan

            @can('manage-roles')
            <!-- Roles y Permisos -->
            <a href="{{ route('roles.index') }}"
               class="sidebar-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Roles y Permisos
            </a>
            @endcan

            @can('manage-settings')
            <!-- Configuración -->
            <a href="{{ route('settings.index') }}"
               class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Configuración
            </a>
            @endcan

            @role('super-admin')
            <!-- Herramientas -->
            <div class="pt-5 pb-2">
                <span class="px-3 text-[10px] font-bold uppercase tracking-[0.15em] text-white/50">
                    Herramientas
                </span>
            </div>
            <!-- Generador de CRUD -->
            <a href="{{ route('crud-generator.index') }}"
               class="sidebar-link {{ request()->routeIs('crud-generator.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
                Generador CRUD
            </a>
            @endrole

            <!-- Config -->
            <div class="pt-5 pb-2">
                <span class="px-3 text-[10px] font-bold uppercase tracking-[0.15em] text-white/50">
                    Cuenta
                </span>
            </div>

            <!-- Perfil -->
            <a href="{{ route('profile.show') }}"
               class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <svg class="w-4 h-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Mi Perfil
            </a>
        </div>

        <!-- User card bottom -->
        <div class="px-4 py-4 border-t border-white/5 mt-auto">
            <div class="hs-dropdown [--placement:top-left] relative w-full inline-flex">
                <button type="button" class="hs-dropdown-toggle w-full flex items-center gap-x-3 py-2 px-2.5 text-sm text-white rounded-xl hover:bg-white/5 transition-all border border-transparent hover:border-white/10 group">
                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-xs font-bold shadow-lg shadow-blue-500/20">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </span>
                    <div class="ms-3 text-start">
                        <span class="block text-sm font-semibold text-white">{{ Auth::user()->name }}</span>
                        <span class="block text-[11px] text-slate-300 font-medium">{{ Auth::user()->getRoleNames()->first() ?? 'Sin rol' }}</span>
                    </div>
                    <svg class="ms-2 w-4 h-4 text-slate-300" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </button>

                <div class="hs-dropdown-menu hs-dropdown-open:opacity-100 w-60 transition-[opacity,margin] duration opacity-0 hidden z-10 bg-white shadow-md rounded-xl p-2 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 mb-2" role="menu">
                    <div class="px-3 py-2 mb-1">
                        <p class="text-xs font-semibold text-slate-800 dark:text-white">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</p>
                    </div>
                    <div class="my-1 border-t border-gray-200 dark:border-slate-700"></div>
                    <a href="{{ route('profile.show') }}" class="flex items-center gap-x-3 py-2 px-3 rounded-lg text-sm text-slate-800 hover:bg-gray-100 dark:text-slate-300 dark:hover:bg-slate-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Mi Perfil
                    </a>
                    <div class="my-1 border-t border-gray-200 dark:border-slate-700"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-x-3 py-2 px-3 rounded-lg text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
</div>
{{-- ===== FIN SIDEBAR ===== --}}

{{-- ===== WRAPPER PRINCIPAL ===== --}}
<div class="w-full lg:ps-64 bg-gray-50 dark:bg-slate-950 min-h-screen transition-colors duration-500">

    {{-- ===== TOPBAR ===== --}}
    <div class="sticky top-0 inset-x-0 z-20 bg-slate-900/80 backdrop-blur-xl border-b border-white/5 shadow-sm" style="background-color: rgba(15, 23, 42, 0.8) !important;">
        <div class="flex items-center justify-between px-4 sm:px-6 py-3">
            <!-- Mobile: Toggle Sidebar -->
            <div class="flex items-center gap-x-3">
                <button type="button" class="lg:hidden p-2 inline-flex justify-center items-center gap-x-2 rounded-xl border border-white/20 text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/20 transition-all"
                        data-hs-overlay="#hs-application-sidebar" aria-controls="hs-application-sidebar" aria-label="Toggle navigation">
                    <svg class="flex-shrink-0 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="21" y1="6" y2="6"/><line x1="3" x2="21" y1="12" y2="12"/><line x1="3" x2="21" y1="18" y2="18"/></svg>
                </button>

                <!-- Breadcrumb -->
                @if(isset($header))
                <div class="hidden sm:flex items-center gap-x-1 text-sm text-slate-300">
                    <span class="text-white font-semibold">{{ $header }}</span>
                </div>
                @endif
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-x-2">

                <!-- Notificaciones -->
                <button type="button" class="relative p-2 rounded-lg text-slate-300 hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-brand-500 rounded-full"></span>
                </button>

                <!-- Avatar -->
                <div class="hidden lg:flex items-center gap-x-2">
                    <span class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-xs font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    {{-- ===== FIN TOPBAR ===== --}}

    {{-- ===== CONTENIDO ===== --}}
    <main class="p-4 sm:p-6 lg:p-8 animate-in">
        @if (session('success'))
            <div class="mb-4 p-4 rounded-xl bg-teal-50 border border-teal-200 dark:bg-teal-900/20 dark:border-teal-700 flex items-start gap-x-3">
                <svg class="w-5 h-5 text-teal-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-teal-700 dark:text-teal-300">{{ session('success') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-700 flex items-start gap-x-3">
                <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
            </div>
        @endif

        {{ $slot }}
    </main>
    {{-- ===== FIN CONTENIDO ===== --}}
</div>
{{-- ===== FIN WRAPPER ===== --}}

@stack('modals')
@livewireScripts

<!-- jQuery + DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.min.js"></script>
@stack('scripts')
</body>
</html>
