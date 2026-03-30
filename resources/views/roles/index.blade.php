<x-app-layout>
    <x-slot name="header">Roles y Permisos</x-slot>

    <div class="space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-800 dark:text-white">Roles y Permisos</h1>
                <p class="text-sm text-slate-500">Configura los roles del sistema y sus permisos asociados.</p>
            </div>
            <a href="{{ route('roles.create') }}"
               class="inline-flex items-center gap-x-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-brand-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nuevo Rol
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse($roles as $role)
            <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm hover:shadow-md transition-shadow p-5">
                <!-- Header del card -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-x-3">
                        <div class="p-2.5 rounded-xl
                            @if($role->name === 'super-admin') bg-brand-50 dark:bg-brand-900/30
                            @elseif($role->name === 'admin') bg-blue-50 dark:bg-blue-900/30
                            @elseif($role->name === 'editor') bg-amber-50 dark:bg-amber-900/30
                            @else bg-slate-100 dark:bg-slate-700 @endif">
                            <svg class="w-5 h-5
                                @if($role->name === 'super-admin') text-brand-600 dark:text-brand-400
                                @elseif($role->name === 'admin') text-blue-600 dark:text-blue-400
                                @elseif($role->name === 'editor') text-amber-600 dark:text-amber-400
                                @else text-slate-500 dark:text-slate-400 @endif"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white capitalize">{{ $role->name }}</h3>
                            <p class="text-xs text-slate-400">{{ $role->users_count }} usuario(s)</p>
                        </div>
                    </div>

                    @if($role->name !== 'super-admin')
                    <div class="flex items-center gap-x-1">
                        <a href="{{ route('roles.edit', $role) }}"
                           class="p-1.5 text-slate-400 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 rounded-lg transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form action="{{ route('roles.destroy', $role) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar el rol {{ addslashes($role->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                    @endif
                </div>

                <!-- Permisos del rol -->
                <div class="space-y-1.5">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Permisos</p>
                    @if($role->permissions->isEmpty())
                        <p class="text-xs text-slate-400 italic">Sin permisos asignados</p>
                    @else
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($role->permissions as $permission)
                            <span class="inline-flex items-center py-0.5 px-2 rounded-md text-xs bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                {{ $permission->name }}
                            </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full py-16 flex flex-col items-center gap-3">
                <svg class="w-14 h-14 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <p class="text-sm text-slate-500">No hay roles configurados.</p>
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
