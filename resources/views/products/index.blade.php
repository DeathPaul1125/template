<x-app-layout>
    <x-slot name="header">Products</x-slot>

    <div class="space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-800 dark:text-white">Products</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Lista de Products.</p>
            </div>
            <a href="{{ route('products.create') }}"
               class="inline-flex items-center gap-x-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-brand-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo Product
            </a>
        </div>

        <div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden p-4">
            <table id="dt-product" class="min-w-full" style="width:100%">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Sale Price</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

@push('scripts')
<script>
$(function () {
    $('#dt-product').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('products.data') }}',
        language: { url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json' },
        dom: '<"flex flex-wrap items-center justify-between gap-2 mb-3"lB>frtip',
        buttons: [
            { extend: 'excelHtml5', text: '<svg class="inline w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>Excel', className: 'dt-btn-excel' },
            { extend: 'pdfHtml5',   text: '<svg class="inline w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>PDF',   className: 'dt-btn-pdf',   orientation: 'landscape', pageSize: 'A4' },
            { extend: 'print',      text: '<svg class="inline w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>Imprimir', className: 'dt-btn-print' },
        ],
        columns: [
                    { data: 'name' },
                    { data: 'description' },
                    { data: 'price' },
                    { data: 'sale_price' },
            { data: 'actions', orderable: false, searchable: false }
        ]
    });
});
</script>
@endpush
</x-app-layout>
