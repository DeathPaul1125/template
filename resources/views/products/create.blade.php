<x-app-layout>
    <x-slot name="header">Nuevo Product</x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="mb-5">
            <h1 class="text-xl font-bold text-slate-800 dark:text-white">Crear Product</h1>
            <p class="text-sm text-slate-500">Completa el formulario para agregar un nuevo registro.</p>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm p-6">
            <form action="{{ route('products.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required class="py-3 px-4 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400 @error('name') border-red-500 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Description</label>
                    <textarea id="description" name="description" rows="4" required class="py-3 px-4 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Price</label>
                    <input id="price" type="number" step="0.01" name="price" value="{{ old('price') }}" required class="py-3 px-4 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400 @error('price') border-red-500 @enderror">
                    @error('price')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="sale_price" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Sale Price</label>
                    <input id="sale_price" type="number" step="0.01" name="sale_price" value="{{ old('sale_price') }}" required class="py-3 px-4 block w-full border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-400 @error('sale_price') border-red-500 @enderror">
                    @error('sale_price')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center justify-end gap-x-3 pt-2">
                    <a href="{{ route('products.index') }}" class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-xl border border-gray-200 text-slate-700 hover:bg-gray-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700 transition-all">Cancelar</a>
                    <button type="submit" class="py-2.5 px-5 inline-flex items-center gap-x-2 text-sm font-semibold rounded-xl bg-brand-600 hover:bg-brand-700 text-white transition-all shadow-sm shadow-brand-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
