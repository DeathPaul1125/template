<x-app-layout>
    <x-slot name="header">Configuración del Sistema</x-slot>

    <div class="max-w-4xl mx-auto py-10 sm:px-6 lg:px-8">

        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Card: Identidad -->
            <div class="flex flex-col bg-white border shadow-sm rounded-xl p-4 md:p-5 dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Identidad Visual</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre del Sitio -->
                    <div>
                        <label for="site_name" class="block text-sm font-medium mb-2 dark:text-white">Nombre del Sistema</label>
                        <input type="text" id="site_name" name="site_name"
                               value="{{ \App\Models\Setting::get('site_name', config('app.name')) }}"
                               class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-brand-500 focus:ring-brand-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400 dark:focus:ring-gray-600">
                    </div>

                    <!-- Color de Marca -->
                    <div>
                        <label for="brand_color" class="block text-sm font-medium mb-2 dark:text-white">Color de Marca (Principal)</label>
                        <div class="flex items-center gap-3">
                            <input type="color" id="brand_color" name="brand_color"
                                   value="{{ \App\Models\Setting::get('brand_color', '#0e8ceb') }}"
                                   class="p-1 h-10 w-20 block bg-white border border-gray-200 cursor-pointer rounded-lg disabled:opacity-50 disabled:pointer-events-none dark:bg-slate-900 dark:border-gray-700">
                            <span class="text-xs text-gray-500 uppercase font-mono">{{ \App\Models\Setting::get('brand_color', '#0e8ceb') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Imágenes -->
            <div class="flex flex-col bg-white border shadow-sm rounded-xl p-4 md:p-5 dark:bg-slate-900 dark:border-gray-700 dark:text-gray-400">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Imágenes y Favicon</h3>

                <div class="space-y-6">
                    <!-- Logo -->
                    <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-4">
                        <div class="md:col-span-1">
                            <p class="text-sm font-semibold dark:text-white">Logo del Sistema</p>
                            <p class="text-xs text-gray-500">Se muestra en el Sidebar y PDF.</p>
                        </div>
                        <div class="md:col-span-2 flex items-center gap-4">
                            @if($logo = \App\Models\Setting::get('site_logo'))
                                <img src="{{ $logo }}" alt="Logo" class="h-12 w-auto border rounded p-1 bg-gray-50">
                            @endif
                            <input type="file" name="site_logo" class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-brand-50 file:text-brand-700
                                hover:file:bg-brand-100">
                        </div>
                    </div>

                    <hr class="dark:border-gray-700">

                    <!-- Login BG -->
                    <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-4">
                        <div class="md:col-span-1">
                            <p class="text-sm font-semibold dark:text-white">Imagen de Fondo (Login)</p>
                            <p class="text-xs text-gray-500">Recomendado: 1920x1080px.</p>
                        </div>
                        <div class="md:col-span-2 flex items-center gap-4">
                            @if($bg = \App\Models\Setting::get('login_bg'))
                                <img src="{{ $bg }}" alt="Login BG" class="h-12 w-20 object-cover border rounded bg-gray-50">
                            @endif
                            <input type="file" name="login_bg" class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-brand-50 file:text-brand-700
                                hover:file:bg-brand-100">
                        </div>
                    </div>

                    <hr class="dark:border-gray-700">

                    <!-- Favicon -->
                    <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-4">
                        <div class="md:col-span-1">
                            <p class="text-sm font-semibold dark:text-white">Favicon</p>
                            <p class="text-xs text-gray-500">Ícono de la pestaña del navegador.</p>
                        </div>
                        <div class="md:col-span-2 flex items-center gap-4">
                            @if($favicon = \App\Models\Setting::get('site_favicon'))
                                <img src="{{ $favicon }}" alt="Favicon" class="h-8 w-8 border rounded p-1 bg-gray-50">
                            @endif
                            <input type="file" name="site_favicon" class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-brand-50 file:text-brand-700
                                hover:file:bg-brand-100">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-x-2">
                <button type="submit" class="py-3 px-4 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-brand-600 text-white hover:bg-brand-700 disabled:opacity-50 disabled:pointer-events-none">
                    Guardar Cambios
                </button>
            </div>
        </form>

    </div>
</x-app-layout>
