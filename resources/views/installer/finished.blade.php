@extends('installer.layout')

@section('content')
<div class="sm:mx-auto sm:w-full sm:max-w-md text-center py-6">
    <!-- Success Icon -->
    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-emerald-100 dark:bg-emerald-900/30 mb-8 shadow-inner">
        <svg class="h-10 w-10 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
        </svg>
    </div>

    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
        ¡Instalación Completada!
    </h1>
    <p class="mt-4 text-sm text-slate-500 font-medium leading-relaxed max-w-xs mx-auto">
        Todo está listo. El sistema ha sido configurado correctamente y ya puedes comenzar a trabajar.
    </p>

    <!-- Resumen dinámico de instalación -->
    <div class="mt-10 p-6 bg-slate-50 dark:bg-slate-950/50 rounded-[2rem] border border-slate-100 dark:border-slate-800 text-start space-y-3">
        <h3 class="text-xs font-black text-blue-600 uppercase tracking-[0.2em] text-center mb-4">Resumen de Instalación</h3>

        @php $checkIcon = '<svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'; @endphp

        <!-- Migraciones -->
        @if(!empty($log['migrations']['success']))
        <details class="group">
            <summary class="flex items-center justify-between gap-x-3 text-sm text-slate-700 dark:text-slate-300 py-1 cursor-pointer hover:text-blue-600 transition-colors list-none">
                <div class="flex items-center gap-x-3">
                    {!! $checkIcon !!}
                    <span class="font-semibold">Migraciones ejecutadas</span>
                </div>
                <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </summary>
            <div class="mt-2 ml-8 p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 max-h-48 overflow-y-auto">
                @foreach($log['migrations']['lines'] as $line)
                    @if(trim($line))
                    <p class="text-xs text-slate-500 font-mono leading-relaxed">{{ $line }}</p>
                    @endif
                @endforeach
            </div>
        </details>
        @endif

        <!-- Roles -->
        @if(!empty($log['roles']['success']))
        <div class="flex items-center gap-x-3 text-sm text-slate-700 dark:text-slate-300">
            {!! $checkIcon !!}
            <span class="font-semibold">Rol <span class="text-blue-600 font-mono">super-admin</span> creado</span>
        </div>
        @endif

        <!-- Admin -->
        @if(!empty($log['admin']['success']))
        <div class="flex items-center gap-x-3 text-sm text-slate-700 dark:text-slate-300">
            {!! $checkIcon !!}
            <span class="font-semibold">Administrador: <span class="text-blue-600">{{ $log['admin']['email'] }}</span></span>
        </div>
        @endif

        <!-- Settings -->
        @if(!empty($log['settings']['success']))
        <div class="flex items-center gap-x-3 text-sm text-slate-700 dark:text-slate-300">
            {!! $checkIcon !!}
            <span class="font-semibold">Aplicación: <span class="text-blue-600">{{ $log['settings']['site_name'] }}</span></span>
        </div>
        @endif

        <!-- Fecha instalación -->
        @if(!empty($log['installed_at']))
        <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
            <p class="text-xs text-slate-400 font-medium text-center">Instalado el {{ $log['installed_at'] }}</p>
        </div>
        @endif
    </div>

    <div class="mt-8 flex flex-col items-center gap-y-4">
        <a href="{{ route('login') }}" class="w-full py-5 px-8 inline-flex justify-center items-center gap-x-3 text-sm font-black rounded-3xl border border-transparent bg-blue-600 text-white hover:bg-blue-700 active:scale-95 transition-all shadow-2xl shadow-blue-600/30 uppercase tracking-widest">
            <span>Ir al Inicio de Sesión</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    </div>
</div>
@endsection
