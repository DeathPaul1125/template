<x-app-layout>
    <x-slot name="header">Editar Rol</x-slot>

    <div class="max-w-xl mx-auto">
        <div class="mb-5">
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">Editar Rol</h1>
            <p class="text-sm text-slate-500">Modifica el nombre y permisos del rol <strong class="capitalize">{{ $role->name }}</strong>.</p>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm p-6">
            <form action="{{ route('roles.update', $role) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <!-- Nombre -->
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Nombre del rol</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $role->name) }}" required
                           class="py-3 px-4 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400 @error('name') border-red-500 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <!-- Permisos -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">Permisos</label>
                    <div class="space-y-2 p-4 bg-slate-50 dark:bg-slate-700/30 rounded-xl border border-gray-200 dark:border-slate-600">
                        @foreach($permissions as $permission)
                        <label for="perm_{{ $permission->id }}" class="flex items-center gap-x-3 cursor-pointer group">
                            <input id="perm_{{ $permission->id }}"
                                   type="checkbox"
                                   name="permissions[]"
                                   value="{{ $permission->name }}"
                                   {{ in_array($permission->name, old('permissions', $rolePermissions)) ? 'checked' : '' }}
                                   class="shrink-0 mt-0.5 border-gray-200 rounded text-brand-600 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:checked:bg-brand-500 cursor-pointer">
                            <span class="text-sm text-slate-700 dark:text-slate-300 group-hover:text-brand-600 transition-colors">{{ $permission->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-x-3 pt-2">
                    <a href="{{ route('roles.index') }}"
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
