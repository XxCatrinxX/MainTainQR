<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function index()
    {
        $piezas = Inventario::latest()->get();
        return view('inventario.index', compact('piezas'));
    }

    public function create()
    {
        return view('inventario.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_pieza' => 'required|string|max:150',
            'sku' => 'required|string|unique:inventario,sku',
            'calidad' => 'required|in:original,generica,usada',
            'stock' => 'required|integer|min:0',
            'precio_venta' => 'required|numeric|min:0',
        ]);

        Inventario::create($data);

        return redirect()->route('inventario.index')->with('success', 'Pieza registrada en inventario correctamente.');
    }

    public function edit(Inventario $inventario)
    {
        return view('inventario.edit', compact('inventario'));
    }

    public function update(Request $request, Inventario $inventario)
    {
        $data = $request->validate([
            'nombre_pieza' => 'required|string|max:150',
            'sku' => 'required|string|unique:inventario,sku,' . $inventario->id,
            'calidad' => 'required|in:original,generica,usada',
            'stock' => 'required|integer|min:0',
            'precio_venta' => 'required|numeric|min:0',
        ]);

        $inventario->update($data);

        return redirect()->route('inventario.index')->with('success', 'Pieza actualizada exitosamente.');
    }

    public function destroy(Inventario $inventario)
    {
        $inventario->delete();
        return redirect()->route('inventario.index')->with('success', 'Pieza eliminada.');
    }
}
