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

    <!-- Info Box -->
    <div class="mt-10 p-6 bg-slate-50 dark:bg-slate-950/50 rounded-[2rem] border border-slate-100 dark:border-slate-800 text-start space-y-4">
        <h3 class="text-xs font-black text-blue-600 uppercase tracking-[0.2em] px-2 text-center">Resumen de Configuración</h3>
        <div class="space-y-3">
            <div class="flex items-center gap-x-3 text-sm text-slate-600 dark:text-slate-400">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Base de Datos establecida
            </div>
            <div class="flex items-center gap-x-3 text-sm text-slate-600 dark:text-slate-400">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Administrador creado
            </div>
            <div class="flex items-center gap-x-3 text-sm text-slate-600 dark:text-slate-400">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Entorno configurado (.env)
            </div>
        </div>
    </div>

    <div class="mt-12 flex flex-col items-center gap-y-4">
        <a href="{{ route('login') }}" class="w-full py-5 px-8 inline-flex justify-center items-center gap-x-3 text-sm font-black rounded-3xl border border-transparent bg-blue-600 text-white hover:bg-blue-700 active:scale-95 transition-all shadow-2xl shadow-blue-600/30 uppercase tracking-widest">
            <span>Ir al Inicio de Sesión</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    </div>
</div>
@endsection
