<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    public function index()
    {
        return response()->json(Inventario::latest()->get());
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

        $pieza = Inventario::create($data);
        return response()->json($pieza, 201);
    }

    public function show(Inventario $inventario)
    {
        return response()->json($inventario);
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
        return response()->json($inventario);
    }

    public function destroy(Inventario $inventario)
    {
        $inventario->delete();
        return response()->json(['message' => 'Pieza eliminada']);
    }
}
