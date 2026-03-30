<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index');
    }

    public function data()
    {
        $isSuperAdmin = Auth::user()?->hasRole('super-admin');
        $query = $isSuperAdmin ? Product::withTrashed() : Product::query();

        return DataTables::of($query)
            ->addColumn('status', function ($product) {
                if ($product->trashed()) {
                    return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">'
                        . '<svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>'
                        . 'Eliminado</span>';
                }
                return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">'
                    . '<svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
                    . 'Activo</span>';
            })
            ->addColumn('actions', function ($product) use ($isSuperAdmin) {
                if ($product->trashed()) {
                    $restore     = route('products.restore', $product->id);
                    $forceDelete = route('products.force-delete', $product->id);
                    return '<form action="' . $restore . '" method="POST" style="display:inline">'
                        . csrf_field()
                        . '<button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-all">'
                        . '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>'
                        . 'Restaurar</button></form>'
                        . '<form action="' . $forceDelete . '" method="POST" style="display:inline" onsubmit="return confirm(\'¿Eliminar DEFINITIVAMENTE? Esta acción no se puede deshacer.\');">'
                        . csrf_field() . method_field('DELETE')
                        . '<button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg transition-all ml-1">'
                        . '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                        . 'Eliminar definitivo</button></form>';
                }
                $edit = route('products.edit', $product);
                $del  = route('products.destroy', $product);
                return '<a href="' . $edit . '" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-all">'
                     . '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
                     . 'Editar</a>'
                     . '<form action="' . $del . '" method="POST" style="display:inline" onsubmit="return confirm(\'¿Eliminar?\');">'
                     . csrf_field() . method_field('DELETE')
                     . '<button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition-all ml-1">'
                     . '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
                     . 'Eliminar</button></form>';
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    public function restore(int $id)
    {
        Product::withTrashed()->findOrFail($id)->restore();
        return redirect()->route('products.index')->with('success', 'Producto restaurado correctamente.');
    }

    public function forceDelete(int $id)
    {
        Product::withTrashed()->findOrFail($id)->forceDelete();
        return redirect()->route('products.index')->with('success', 'Producto eliminado definitivamente.');
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric'],
            'sale_price' => ['required', 'numeric'],
        ]);

        Product::create($request->only(['name', 'description', 'price', 'sale_price']));

        return redirect()->route('products.index')
            ->with('success', 'Product creado correctamente.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric'],
            'sale_price' => ['required', 'numeric'],
        ]);

        $product->update($request->only(['name', 'description', 'price', 'sale_price']));

        return redirect()->route('products.index')
            ->with('success', 'Product actualizado correctamente.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product eliminado correctamente.');
    }
}
