@extends('installer.layout')

@section('content')
<div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
        Base de Datos
    </h1>
    <p class="mt-4 text-sm text-slate-500 font-medium leading-relaxed max-w-xs mx-auto">
        Configure los parámetros de conexión para la persistencia del sistema.
    </p>

    <!-- Progress Indicator -->
    <div class="mt-8 flex justify-center items-center gap-x-2">
        <a href="{{ route('install.welcome') }}" class="w-8 h-1.5 bg-blue-100 dark:bg-blue-900 rounded-full cursor-pointer hover:bg-blue-200 transition-colors"></a>
        <div class="w-8 h-1.5 bg-blue-600 rounded-full"></div>
        <div class="w-8 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full"></div>
    </div>
</div>

<div class="mt-12">
    <form action="{{ route('install.saveDatabase') }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- Host -->
            <div class="space-y-2">
                <label for="db_host" class="block text-xs font-black uppercase text-slate-500 tracking-[0.2em] px-2 text-start">
                    Host del Servidor
                </label>
                <input type="text" name="db_host" id="db_host" value="{{ old('db_host', '127.0.0.1') }}"
                       class="py-4 px-6 block w-full bg-slate-50/50 dark:bg-slate-950/50 border-slate-200 dark:border-slate-800 rounded-2xl text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:ring-blue-500/10 transition-all font-medium placeholder-slate-400" 
                       placeholder="127.0.0.1">
            </div>

            <!-- Port -->
            <div class="space-y-2">
                <label for="db_port" class="block text-xs font-black uppercase text-slate-500 tracking-[0.2em] px-2 text-start">
                    Puerto
                </label>
                <input type="text" name="db_port" id="db_port" value="{{ old('db_port', '3306') }}"
                       class="py-4 px-6 block w-full bg-slate-50/50 dark:bg-slate-950/50 border-slate-200 dark:border-slate-800 rounded-2xl text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:ring-blue-500/10 transition-all font-medium placeholder-slate-400" 
                       placeholder="3306">
            </div>
        </div>

        <!-- DB Name -->
        <div class="space-y-2">
            <label for="db_database" class="block text-xs font-black uppercase text-slate-500 tracking-[0.2em] px-2 text-start">
                Base de Datos
            </label>
            <input type="text" name="db_database" id="db_database" value="{{ old('db_database') }}"
                   class="py-4 px-6 block w-full bg-slate-50/50 dark:bg-slate-950/50 border-slate-200 dark:border-slate-800 rounded-2xl text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:ring-blue-500/10 transition-all font-medium placeholder-slate-400" 
                   placeholder="ej. mi_database">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- Username -->
            <div class="space-y-2">
                <label for="db_username" class="block text-xs font-black uppercase text-slate-500 tracking-[0.2em] px-2 text-start">
                    Usuario
                </label>
                <input type="text" name="db_username" id="db_username" value="{{ old('db_username', 'root') }}"
                       class="py-4 px-6 block w-full bg-slate-50/50 dark:bg-slate-950/50 border-slate-200 dark:border-slate-800 rounded-2xl text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:ring-blue-500/10 transition-all font-medium placeholder-slate-400" 
                       placeholder="root">
            </div>

            <!-- Password -->
            <div class="space-y-2">
                <label for="db_password" class="block text-xs font-black uppercase text-slate-500 tracking-[0.2em] px-2 text-start">
                    Contraseña
                </label>
                <input type="password" name="db_password" id="db_password"
                       class="py-4 px-6 block w-full bg-slate-50/50 dark:bg-slate-950/50 border-slate-200 dark:border-slate-800 rounded-2xl text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:ring-blue-500/10 transition-all font-medium placeholder-slate-400" 
                       placeholder="••••••••">
            </div>
        </div>

        @if ($errors->any())
        <div class="p-4 rounded-xl bg-red-50 border border-red-100 animate-in">
            <ul class="text-xs text-red-600 font-bold space-y-1 px-4">
                @foreach ($errors->all() as $error)
                    <li class="flex items-center gap-x-2">
                        <span class="w-1 h-1 bg-red-400 rounded-full"></span>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="flex justify-between items-center pt-8">
            <a href="{{ route('install.welcome') }}" class="group text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-700 transition-all flex items-center gap-x-3 px-4">
                <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 17l-5-5m0 0l5-5m-5 5h12" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Regresar
            </a>
            <button type="submit" class="group py-4 px-12 inline-flex justify-center items-center gap-x-4 text-sm font-black rounded-2xl border border-transparent bg-blue-600 text-white hover:bg-blue-700 active:scale-95 transition-all shadow-xl shadow-blue-600/20 uppercase tracking-widest">
                Siguiente
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
    </form>
</div>
@endsection
