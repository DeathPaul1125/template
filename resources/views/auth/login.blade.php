<x-guest-layout>
<div class="min-h-screen flex">
    <!-- Left panel: Brand / Illustration -->
    @php
        $loginBg = \App\Models\Setting::get('login_bg');
    @endphp
    <!-- Left panel: Brand / Illustration -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-brand-600 via-brand-700 to-slate-900 relative overflow-hidden flex-col items-center justify-center p-12"
         @if($loginBg) style="background-image: url('{{ $loginBg }}'); background-size: cover; background-position: center;" @endif>
        
        @if($loginBg)
            <!-- Dark overlay if bg image exists -->
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-[2px]"></div>
        @endif
        <!-- Background decoration -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-brand-400/10 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full"
                 style="background: radial-gradient(ellipse at center, rgba(99,102,241,0.15) 0%, transparent 70%)"></div>
        </div>

        <!-- Grid pattern overlay -->
        <div class="absolute inset-0 opacity-10"
             style="background-image: linear-gradient(rgba(255,255,255,.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.1) 1px, transparent 1px); background-size: 40px 40px;"></div>

        <div class="relative z-10 text-center">
            <!-- Logo -->
            <div class="mb-8 inline-flex items-center justify-center w-20 h-20 bg-white/10 backdrop-blur-sm rounded-3xl border border-white/20 shadow-2xl overflow-hidden">
                @if($logo = \App\Models\Setting::get('site_logo'))
                    <img src="{{ $logo }}" alt="Logo" class="w-12 h-12 object-contain">
                @else
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                @endif
            </div>

            <h1 class="text-4xl font-bold text-white mb-4 leading-tight">
                {{ \App\Models\Setting::get('site_name', config('app.name')) }}
            </h1>
            <p class="text-brand-200 text-lg max-w-sm mx-auto leading-relaxed">
                Panel de administración seguro y eficiente para gestionar tu sistema.
            </p>

            <!-- Feature pills -->
            <div class="mt-10 flex flex-col gap-3 items-center">
                @foreach([
                    ['icon' => '🛡️', 'text' => 'Roles y permisos avanzados'],
                    ['icon' => '⚡', 'text' => 'Interfaz rápida y moderna'],
                    ['icon' => '👥', 'text' => 'Gestión completa de usuarios'],
                ] as $feature)
                <div class="flex items-center gap-x-3 px-4 py-2.5 bg-white/10 backdrop-blur-sm rounded-xl border border-white/10 text-sm text-white">
                    <span>{{ $feature['icon'] }}</span>
                    <span>{{ $feature['text'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Right panel: Login form -->
    <div class="flex-1 flex items-center justify-center p-6 sm:p-12">
        <div class="w-full max-w-md">
            <!-- Mobile logo -->
            <div class="lg:hidden flex items-center gap-x-3 mb-10">
                @if($logo = \App\Models\Setting::get('site_logo'))
                    <img src="{{ $logo }}" alt="Logo" class="w-10 h-10 object-contain">
                @else
                    <div class="w-10 h-10 bg-gradient-to-br from-brand-500 to-brand-700 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                @endif
                <span class="text-xl font-bold text-slate-800 dark:text-white">{{ \App\Models\Setting::get('site_name', config('app.name')) }}</span>
            </div>

            <!-- Header -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-slate-800 dark:text-white">
                    Bienvenido de vuelta 👋
                </h2>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Inicia sesión para acceder al panel de control.
                </p>
            </div>

            <!-- Errors -->
            @if ($errors->any())
            <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-700">
                <div class="flex items-start gap-x-3">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <ul class="text-sm text-red-600 dark:text-red-400 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            @session('status')
            <div class="mb-5 p-4 rounded-xl bg-teal-50 border border-teal-200 dark:bg-teal-900/20 dark:border-teal-700">
                <p class="text-sm text-teal-700 dark:text-teal-300">{{ $value }}</p>
            </div>
            @endsession

            <!-- Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Correo electrónico
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                               required autofocus autocomplete="username"
                               class="py-3 ps-10 pe-4 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400 dark:placeholder-slate-500 dark:focus:ring-slate-600 @error('email') border-red-500 @enderror"
                               placeholder="tu@email.com">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                        Contraseña
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input id="password" type="password" name="password"
                               required autocomplete="current-password"
                               class="py-3 ps-10 pe-4 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400 dark:placeholder-slate-500 dark:focus:ring-slate-600 @error('password') border-red-500 @enderror"
                               placeholder="••••••••">
                    </div>
                </div>

                <!-- Remember + Forgot -->
                <div class="flex items-center justify-between">
                    <label for="remember_me" class="flex items-center gap-x-2 cursor-pointer">
                        <input id="remember_me" type="checkbox" name="remember"
                               class="shrink-0 mt-0.5 border-gray-200 rounded text-brand-600 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:checked:bg-brand-500 dark:checked:border-brand-500 dark:focus:ring-offset-slate-800 cursor-pointer">
                        <span class="text-sm text-slate-600 dark:text-slate-400">Recordarme</span>
                    </label>
                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-sm text-brand-600 hover:text-brand-700 font-medium dark:text-brand-400 dark:hover:text-brand-300 transition-colors">
                        ¿Olvidaste tu contraseña?
                    </a>
                    @endif
                </div>

                <!-- Submit -->
                <button type="submit"
                        class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-xl border border-transparent bg-brand-600 text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none dark:focus:ring-offset-slate-800 transition-all shadow-sm shadow-brand-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Iniciar Sesión
                </button>
            </form>

            <!-- Footer -->
            <p class="mt-8 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
            </p>
        </div>
    </div>
</div>
</x-guest-layout>
