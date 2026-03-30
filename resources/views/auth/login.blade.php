<x-guest-layout>
    <div class="bg-white dark:bg-slate-900 py-10 px-6 shadow-2xl shadow-slate-200 dark:shadow-none sm:rounded-[2.5rem] sm:px-12 border border-slate-100 dark:border-slate-800 relative overflow-hidden group">
        <!-- Decoration from installer look -->
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-blue-600/5 rounded-full blur-2xl group-hover:bg-blue-600/10 transition-all duration-700"></div>

        <div class="relative z-10">
            <!-- Logo area -->
            <div class="flex flex-col items-center mb-10 text-center">
                <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center shadow-xl shadow-blue-600/20 mb-6 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Acceso al Sistema</h1>
                <p class="mt-3 text-sm text-slate-500 font-medium leading-relaxed">Bienvenido de vuelta. Por favor, ingrese sus credenciales para continuar.</p>
            </div>

            <!-- Validation Errors Header -->
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-100 animate-in">
                    <div class="text-xs font-black uppercase text-red-600 tracking-widest mb-2 px-1">Se encontraron errores</div>
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="flex items-center gap-x-2 text-xs font-bold text-red-700">
                                <span class="w-1 h-1 bg-red-400 rounded-full"></span>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-xs font-bold text-emerald-700 animate-in">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Email Address -->
                <div class="space-y-2">
                    <label for="email" class="block text-xs font-black uppercase text-slate-500 tracking-[0.2em] px-2 text-start">
                        Correo Electrónico
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           class="py-4 px-6 block w-full bg-slate-50/50 dark:bg-slate-950/50 border-slate-200 dark:border-slate-800 rounded-2xl text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:ring-blue-500/10 transition-all font-medium placeholder-slate-400"
                           placeholder="usuario@dominio.com">
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <div class="flex justify-between items-center px-2">
                        <label for="password" class="block text-xs font-black uppercase text-slate-500 tracking-[0.2em] text-start">
                            Contraseña
                        </label>
                        @if (Route::has('password.request'))
                            <a class="text-[10px] font-black uppercase tracking-tighter text-blue-600 hover:text-blue-500 transition-colors" href="{{ route('password.request') }}">
                                ¿Olvidó su contraseña?
                            </a>
                        @endif
                    </div>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           class="py-4 px-6 block w-full bg-slate-50/50 dark:bg-slate-950/50 border-slate-200 dark:border-slate-800 rounded-2xl text-sm text-slate-900 dark:text-white focus:border-blue-500 focus:ring-blue-500/10 transition-all font-medium placeholder-slate-400"
                           placeholder="••••••••">
                </div>

                <!-- Remember Me -->
                <div class="block px-2 text-start">
                    <label for="remember_me" class="inline-flex items-center group cursor-pointer">
                        <input id="remember_me" type="checkbox" class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 text-blue-600 shadow-sm focus:ring-blue-500/20 dark:bg-slate-900 cursor-pointer" name="remember">
                        <span class="ms-3 text-xs font-bold text-slate-500 group-hover:text-slate-700 transition-colors">Mantener sesión iniciada</span>
                    </label>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full py-4 px-6 inline-flex justify-center items-center gap-x-3 text-sm font-black rounded-2xl border border-transparent bg-blue-600 text-white hover:bg-blue-700 active:scale-95 transition-all shadow-xl shadow-blue-600/20 uppercase tracking-widest">
                        <span>Iniciar Sesión</span>
                        <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </button>
                </div>
            </form>
            
            <p class="mt-10 text-center text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                &copy; {{ date('Y') }} Acceso Protegido
            </p>
        </div>
    </div>
</x-guest-layout>
