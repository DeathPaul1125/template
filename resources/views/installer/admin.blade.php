@extends('installer.layout')

@section('content')
<div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
        Configuración Final
    </h1>
    <p class="mt-4 text-sm text-slate-500 font-medium leading-relaxed max-w-xs mx-auto">
        Personalice el nombre de su aplicación y cree su cuenta de administrador.
    </p>

    <!-- Progress Indicator -->
    <div class="mt-8 flex justify-center items-center gap-x-2">
        <a href="{{ route('install.welcome') }}" class="w-8 h-1.5 bg-blue-100 dark:bg-blue-900 rounded-full cursor-pointer hover:bg-blue-200 transition-colors"></a>
        <a href="{{ route('install.database') }}" class="w-8 h-1.5 bg-blue-100 dark:bg-blue-900 rounded-full cursor-pointer hover:bg-blue-200 transition-colors"></a>
        <div class="w-8 h-1.5 bg-blue-600 rounded-full"></div>
    </div>
</div>

<div class="mt-12">
    <form action="{{ route('install.saveAdmin') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Site Name -->
        <div class="space-y-2 text-start">
            <label for="site_name" class="block text-xs font-black uppercase text-slate-500 tracking-[0.2em] px-2">
                Nombre del Sitio
            </label>
            <input type="text" name="site_name" id="site_name" value="{{ old('site_name', config('app.name')) }}"
                   class="py-4 px-6 block w-full bg-slate-50/50 dark:bg-slate-950/50 border-slate-200 dark:border-slate-800 rounded-2xl text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:ring-blue-500/10 transition-all font-medium placeholder-slate-400"
                   placeholder="Mi Aplicación">
        </div>

        <!-- App URL -->
        <div class="space-y-2 text-start">
            <label for="app_url" class="block text-xs font-black uppercase text-slate-500 tracking-[0.2em] px-2">
                URL de la Aplicación
            </label>
            <input type="url" name="app_url" id="app_url" value="{{ old('app_url', config('app.url')) }}"
                   class="py-4 px-6 block w-full bg-slate-50/50 dark:bg-slate-950/50 border-slate-200 dark:border-slate-800 rounded-2xl text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:ring-blue-500/10 transition-all font-medium placeholder-slate-400"
                   placeholder="http://localhost">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-start">
            <!-- Admin Name -->
            <div class="space-y-2">
                <label for="admin_name" class="block text-xs font-black uppercase text-slate-500 tracking-[0.2em] px-2">
                    Nombre del Administrador
                </label>
                <input type="text" name="admin_name" id="admin_name" value="{{ old('admin_name') }}"
                       class="py-4 px-6 block w-full bg-slate-50/50 dark:bg-slate-950/50 border-slate-200 dark:border-slate-800 rounded-2xl text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:ring-blue-500/10 transition-all font-medium placeholder-slate-400"
                       placeholder="Nombre Completo">
            </div>

            <!-- Admin Email -->
            <div class="space-y-2">
                <label for="admin_email" class="block text-xs font-black uppercase text-slate-500 tracking-[0.2em] px-2">
                    Correo Electrónico
                </label>
                <input type="email" name="admin_email" id="admin_email" value="{{ old('admin_email') }}"
                       class="py-4 px-6 block w-full bg-slate-50/50 dark:bg-slate-950/50 border-slate-200 dark:border-slate-800 rounded-2xl text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:ring-blue-500/10 transition-all font-medium placeholder-slate-400"
                       placeholder="admin@ejemplo.com">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-start">
            <!-- Password -->
            <div class="space-y-2">
                <label for="admin_password" class="block text-xs font-black uppercase text-slate-500 tracking-[0.2em] px-2">
                    Contraseña
                </label>
                <input type="password" name="admin_password" id="admin_password"
                       class="py-4 px-6 block w-full bg-slate-50/50 dark:bg-slate-950/50 border-slate-200 dark:border-slate-800 rounded-2xl text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:ring-blue-500/10 transition-all font-medium placeholder-slate-400"
                       placeholder="••••••••">
            </div>

            <!-- Password Confirmation -->
            <div class="space-y-2">
                <label for="admin_password_confirmation" class="block text-xs font-black uppercase text-slate-500 tracking-[0.2em] px-2">
                    Confirmar Contraseña
                </label>
                <input type="password" name="admin_password_confirmation" id="admin_password_confirmation"
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
            <a href="{{ route('install.database') }}" class="group text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-700 transition-all flex items-center gap-x-3 px-4">
                <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 17l-5-5m0 0l5-5m-5 5h12" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Regresar
            </a>
            <button type="submit" class="group py-4 px-12 inline-flex justify-center items-center gap-x-4 text-sm font-black rounded-2xl border border-transparent bg-blue-600 text-white hover:bg-blue-700 active:scale-95 transition-all shadow-xl shadow-blue-600/20 uppercase tracking-widest">
                Finalizar Instalación
                <svg class="w-6 h-6 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
    </form>
</div>
@endsection
