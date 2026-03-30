<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index');
    }

    public function data()
    {
        $query = Product::query();

        return DataTables::of($query)
            ->addColumn('actions', function ($product) {
                $edit = route('products.edit', $product);
                $del  = route('products.destroy', $product);
                return '<a href="' . $edit . '" class="p-1 text-brand-600 hover:underline text-xs">Editar</a>'
                     . '<form action="' . $del . '" method="POST" style="display:inline" onsubmit="return confirm(\'¿Eliminar?\')">' . csrf_field() . method_field('DELETE')
                     . '<button type="submit" class="p-1 text-red-500 hover:underline text-xs ml-2">Eliminar</button></form>';
            })
            ->rawColumns(['actions'])
            ->make(true);
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
