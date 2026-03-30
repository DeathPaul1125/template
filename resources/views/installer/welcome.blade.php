@extends('installer.layout')

@section('content')
<div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
        Bienvenido al Instalador
    </h1>
    <p class="mt-4 text-sm text-slate-500 font-medium leading-relaxed max-w-xs mx-auto">
        Antes de empezar, necesitamos verificar que el servidor cumpla con los requisitos mínimos de infraestructura.
    </p>

    <!-- Progress Indicator -->
    <div class="mt-8 flex justify-center items-center gap-x-2">
        <div class="w-8 h-1.5 bg-blue-600 rounded-full"></div>
        <div class="w-8 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full"></div>
        <div class="w-8 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full"></div>
    </div>
</div>

<div class="mt-12 space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @php $allMet = true; @endphp
        @foreach($requirements as $label => $met)
            @php if(!$met) $allMet = false; @endphp
            <div class="flex items-center justify-between p-4 rounded-2xl border transition-all shadow-sm" style="background-color: {{ $met ? '#f0fdf4' : '#fef2f2' }}; border-color: {{ $met ? '#bbf7d0' : '#fecaca' }};">
                <span class="text-xs font-bold tracking-wide uppercase" style="color: {{ $met ? '#15803d' : '#b91c1c' }};">{{ $label }}</span>
                <div class="flex-shrink-0">
                    @if($met)
                        <div class="w-7 h-7 rounded-full flex items-center justify-center shadow-lg" style="background-color: #10b981; box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3);">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                    @else
                        <div class="w-7 h-7 rounded-full flex items-center justify-center shadow-lg" style="background-color: #ef4444; box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.3);">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if(!$allMet)
    <div class="p-4 rounded-xl bg-amber-50 border border-amber-200">
        <p class="text-xs text-amber-700 font-bold text-center">Existen requisitos no cumplidos. No es posible continuar.</p>
    </div>
    @endif

    <div class="pt-8">
        @if($allMet)
            <a href="{{ route('install.database') }}" class="w-full py-4 px-6 inline-flex justify-center items-center gap-x-2 text-sm font-black rounded-2xl border border-transparent bg-blue-600 text-white hover:bg-blue-700 active:scale-95 transition-all shadow-xl shadow-blue-600/20 uppercase tracking-widest">
                <span>Comenzar Configuración</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        @else
            <button disabled class="w-full py-4 px-6 inline-flex justify-center items-center gap-x-2 text-sm font-black rounded-2xl border border-transparent bg-slate-100 text-slate-400 cursor-not-allowed uppercase tracking-widest">
                Requisitos incompletos
            </button>
        @endif
    </div>
</div>
@endsection
