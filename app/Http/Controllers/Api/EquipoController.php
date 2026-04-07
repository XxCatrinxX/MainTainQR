<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipo;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    public function index()
    {
        return response()->json(Equipo::with('cliente')->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'tipo' => 'required|string|max:100',
            'marca' => 'required|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'serie' => 'nullable|string|max:100',
        ]);

        $equipo = Equipo::create($data);
        return response()->json($equipo, 201);
    }

    public function show(Equipo $equipo)
    {
        return response()->json($equipo->load(['cliente', 'ordenesServicio']));
    }

    public function update(Request $request, Equipo $equipo)
    {
        $data = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'tipo' => 'required|string|max:100',
            'marca' => 'required|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'serie' => 'nullable|string|max:100',
        ]);

        $equipo->update($data);
        return response()->json($equipo);
    }

    public function destroy(Equipo $equipo)
    {
        $equipo->delete();
        return response()->json(['message' => 'Equipo eliminado']);
    }
}
