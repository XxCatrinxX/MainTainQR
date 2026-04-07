<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SolicitudCompra;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SolicitudCompraController extends Controller
{
    public function index()
    {
        return response()->json(SolicitudCompra::with(['ordenServicio.equipo', 'usuario'])->latest()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'orden_servicio_id' => 'required|exists:orden_servicios,id',
            'nombre_pieza' => 'required|string|max:255',
            'cantidad' => 'required|integer|min:1',
            'descripcion' => 'nullable|string',
        ]);

        $solicitud = SolicitudCompra::create([
            'orden_servicio_id' => $request->orden_servicio_id,
            'user_id' => Auth::id(),
            'nombre_pieza' => $request->nombre_pieza,
            'cantidad' => $request->cantidad,
            'descripcion' => $request->descripcion,
            'estado' => 'pendiente',
        ]);

        return response()->json($solicitud, 201);
    }

    public function surtir(Request $request, $id)
    {
        $request->validate([
            'cantidad_recibida' => 'required|integer|min:1',
            'precio_venta' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:50',
            'calidad' => 'nullable|string|max:50',
        ]);

        // Logic from the web controller adapted for JSON
        $solicitud = SolicitudCompra::findOrFail($id);
        
        $sku = $request->sku ?: 'SKU-' . strtoupper(substr(uniqid(), -6));
        
        $inventario = Inventario::updateOrCreate(
            ['nombre_pieza' => $solicitud->nombre_pieza],
            [
                'sku' => $sku,
                'calidad' => $request->calidad ?: 'Genérica',
                'precio_venta' => $request->precio_venta,
                'stock' => DB::raw("stock + " . $request->cantidad_recibida)
            ]
        );

        $solicitud->update([
            'estado' => 'surtido',
            'cantidad' => $request->cantidad_recibida
        ]);

        return response()->json([
            'message' => 'Pieza surtida con éxito',
            'solicitud' => $solicitud,
            'inventario' => $inventario
        ]);
    }
}
