<x-app-layout>
    <x-slot name="header">Editar Usuario</x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="mb-5">
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">Editar Usuario</h1>
            <p class="text-sm text-slate-500">Modifica los datos del usuario <strong>{{ $user->name }}</strong>.</p>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm p-6">
            <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <!-- Nombre -->
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nombre completo</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="py-3 px-4 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400 @error('name') border-red-500 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Correo electrónico</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="py-3 px-4 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400 @error('email') border-red-500 @enderror">
                    @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <!-- Nueva Contraseña (opcional) -->
                <div class="p-4 bg-slate-50 dark:bg-slate-700/30 rounded-xl border border-dashed border-slate-200 dark:border-slate-600">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Cambiar Contraseña (opcional)</p>
                    <div class="space-y-4">
                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nueva contraseña</label>
                            <input id="password" type="password" name="password"
                                   class="py-3 px-4 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400 @error('password') border-red-500 @enderror"
                                   placeholder="Dejar vacío para no cambiar">
                            @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Confirmar nueva contraseña</label>
                            <input id="password_confirmation" type="password" name="password_confirmation"
                                   class="py-3 px-4 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400"
                                   placeholder="Dejar vacío para no cambiar">
                        </div>
                    </div>
                </div>

                <!-- Rol -->
                <div>
                    <label for="role" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Rol del usuario</label>
                    <select id="role" name="role" required
                            class="py-3 px-4 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400 @error('role') border-red-500 @enderror">
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ $currentRole === $role->name ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                        </option>
                        @endforeach
                    </select>
                    @error('role')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <!-- Botones -->
                <div class="flex items-center justify-end gap-x-3 pt-2">
                    <a href="{{ route('users.index') }}"
                       class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-xl border border-gray-200 text-slate-700 hover:bg-gray-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700 transition-all">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="py-2.5 px-5 inline-flex items-center gap-x-2 text-sm font-semibold rounded-xl bg-brand-600 hover:bg-brand-700 text-white transition-all shadow-sm shadow-brand-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
