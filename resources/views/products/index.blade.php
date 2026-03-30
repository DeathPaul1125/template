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

        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
            <div class="p-5">
                <table id="dt-product" class="w-full" style="width:100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Sale Price</th>
                            @if(auth()->user()?->hasRole('super-admin'))
                            <th>Estado</th>
                            @endif
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
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
        dom: '<"flex flex-wrap items-center justify-between gap-3 mb-4"lB><"mb-3"f>rt<"flex flex-wrap items-center justify-between gap-3 mt-4"ip>',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<svg class="inline w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>Excel',
                className: 'dt-btn-excel',
                exportOptions: { columns: ':not(:last-child)' }
            },
            {
                extend: 'pdfHtml5',
                text: '<svg class="inline w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>PDF',
                className: 'dt-btn-pdf',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: { columns: ':not(:last-child)' },
                customize: function(doc) {
                    doc.pageMargins = [30, 45, 30, 50];
                    if (doc.content[0]) {
                        doc.content[0].fontSize = 16;
                        doc.content[0].bold = true;
                        doc.content[0].color = '#1e293b';
                        doc.content[0].margin = [0, 0, 0, 14];
                    }
                    var tbl  = doc.content[doc.content.length - 1];
                    var body = tbl.table.body;
                    // Encabezado con fondo degradado indigo
                    body[0].forEach(function(c) {
                        c.fillColor = '#4f46e5';
                        c.color     = '#ffffff';
                        c.bold      = true;
                        c.fontSize  = 8;
                        c.margin    = [5, 7, 5, 7];
                    });
                    // Filas alternadas
                    for (var i = 1; i < body.length; i++) {
                        body[i].forEach(function(c) {
                            c.fontSize  = 8;
                            c.color     = '#334155';
                            c.fillColor = i % 2 === 0 ? '#f1f5f9' : '#ffffff';
                            c.margin    = [5, 5, 5, 5];
                        });
                    }
                    // Bordes minimalistas
                    tbl.layout = {
                        hLineWidth: function(i, n) { return (i === 0 || i === 1 || i === n.table.body.length) ? 0 : 0.5; },
                        vLineWidth: function()       { return 0; },
                        hLineColor: function()       { return '#e2e8f0'; }
                    };
                    tbl.table.widths = Array(body[0].length).fill('*');
                    // Pie de página
                    doc.footer = function(p, n) {
                        return {
                            columns: [
                                { text: new Date().toLocaleDateString('es-ES', {day:'2-digit',month:'long',year:'numeric'}), fontSize: 7, color: '#94a3b8', margin: [30, 0, 0, 0] },
                                { text: 'Página ' + p + ' de ' + n, fontSize: 7, color: '#94a3b8', alignment: 'right', margin: [0, 0, 30, 0] }
                            ],
                            margin: [0, 10, 0, 0]
                        };
                    };
                }
            },
            {
                extend: 'print',
                text: '<svg class="inline w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>Imprimir',
                className: 'dt-btn-print',
                exportOptions: { columns: ':not(:last-child)' }
            },
        ],
        columns: [
            { data: 'name' },
            { data: 'description' },
            { data: 'price' },
            { data: 'sale_price' },
            @if(auth()->user()?->hasRole('super-admin'))
            { data: 'status', orderable: false, searchable: false },
            @endif
            { data: 'actions', orderable: false, searchable: false }
        ]
    });
});
</script>
@endpush
</x-app-layout>
