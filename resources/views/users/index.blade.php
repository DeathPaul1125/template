<x-app-layout>
    <x-slot name="header">Gestión de Usuarios</x-slot>

    <div class="space-y-5">
        <!-- Topbar actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-800 dark:text-white">Usuarios</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Administra los usuarios del sistema y asigna roles.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('users.print') }}" target="_blank"
                   class="inline-flex items-center gap-x-2 px-4 py-2.5 bg-white border border-gray-200 text-slate-700 hover:bg-gray-50 text-sm font-semibold rounded-xl transition-all shadow-sm dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.89l-2.007-.139a.747.747 0 01-.65-.796c.003-.11.02-.219.05-.324l.35-1.142A2.746 2.746 0 016.94 9.54l1.115-.115M17.28 13.89l2.007-.139a.747.747 0 00.65-.796c-.003-.11-.02-.219-.05-.324l-.35-1.142M17.06 9.425l-1.115-.115M6.94 9.54c.433-.045.882-.069 1.334-.076M17.06 9.425c-.433-.045-.882-.069-1.334-.076M8.274 9.464a49.103 49.103 0 017.452 0m-7.452 0l-.587 3.522m7.452 0l.587 3.522M8.274 9.464c.148-1.169 1.107-2.087 2.278-2.26a48.188 48.188 0 011.898-.121c.633-.017 1.265-.017 1.898.121 1.171.173 2.13 1.091 2.278 2.26m-9.45 6.044a2.25 2.25 0 00-2.241 2.25v2.25a2.25 2.25 0 002.25 2.25h11.25a2.25 2.25 0 002.25-2.25v-2.25a2.25 2.25 0 00-2.241-2.25M8.274 15.508l-.587 3.522m7.452-3.522l.587 3.522M12 9v6m-2.25 0a2.25 2.25 0 104.5 0h-4.5z" />
                    </svg>
                    Imprimir PDF
                </a>
                <a href="{{ route('users.create') }}"
                   class="inline-flex items-center gap-x-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-brand-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nuevo Usuario
                </a>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                    <thead class="bg-gray-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="px-6 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Usuario</th>
                            <th class="px-6 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Rol</th>
                            <th class="px-6 py-3.5 text-start text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Registro</th>
                            <th class="px-6 py-3.5 text-end text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                        @forelse($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-x-3">
                                    <span class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @foreach($user->roles as $role)
                                <span class="inline-flex items-center gap-x-1 py-1 px-2.5 rounded-full text-xs font-medium
                                    @if($role->name === 'super-admin') bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400
                                    @elseif($role->name === 'admin') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                                    @elseif($role->name === 'editor') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                                    @else bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 @endif">
                                    {{ $role->name }}
                                </span>
                                @endforeach
                                @if($user->roles->isEmpty())
                                <span class="text-xs text-slate-400">Sin rol</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-500 dark:text-slate-400">{{ $user->created_at->format('d/m/Y') }}</span>
                            </td>
                            <td class="px-6 py-4 text-end">
                                <div class="inline-flex items-center gap-x-1">
                                    <a href="{{ route('users.edit', $user) }}"
                                       class="p-2 text-slate-500 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 rounded-lg transition-all"
                                       title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar al usuario {{ addslashes($user->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="p-2 text-slate-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all"
                                                title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <p class="text-sm text-slate-500">No hay usuarios registrados.</p>
                                    <a href="{{ route('users.create') }}" class="text-sm text-brand-600 hover:underline font-medium">Crear primer usuario</a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
