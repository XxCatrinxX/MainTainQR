<?php

namespace App\Http\Controllers;

use App\Models\SolicitudCompra;
use App\Models\Inventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SolicitudCompraController extends Controller
{
    public function index()
    {
        $solicitudes = SolicitudCompra::with(['ordenServicio.equipo', 'usuario'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        return view('solicitudes.index', compact('solicitudes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'orden_servicio_id' => 'required|exists:orden_servicios,id',
            'nombre_pieza' => 'required|string|max:255',
            'cantidad' => 'required|integer|min:1',
            'descripcion' => 'nullable|string',
        ]);

        SolicitudCompra::create([
            'orden_servicio_id' => $request->orden_servicio_id,
            'user_id' => Auth::id(),
            'nombre_pieza' => $request->nombre_pieza,
            'cantidad' => $request->cantidad,
            'descripcion' => $request->descripcion,
            'estado' => 'pendiente',
        ]);

        return back()->with('success', 'Solicitud de compra enviada al almacén.');
    }

    public function surtir(Request $request, $id)
    {
        dd('¡LLEGAMOS AL SURTIR!', $request->all(), $id);
        $request->validate([
            'cantidad_recibida' => 'required|integer|min:1',
            'precio_venta' => 'required|numeric|min:0',
            'sku' => 'nullable|string|max:50',
            'calidad' => 'nullable|string|max:50',
        ]);

        $solicitud = SolicitudCompra::findOrFail($id);
        
        // Buscar por SKU si se proporcionó, o por nombre exacto
        $sku = $request->sku ?: 'SKU-' . strtoupper(substr(uniqid(), -6));
        
        $inventario = Inventario::updateOrCreate(
            ['nombre_pieza' => $solicitud->nombre_pieza],
            [
                'sku' => $sku,
                'calidad' => $request->calidad ?: 'Genérica',
                'precio_venta' => $request->precio_venta,
                'stock' => \DB::raw("stock + " . $request->cantidad_recibida)
            ]
        );

        $solicitud->update([
            'estado' => 'surtido',
            'cantidad' => $request->cantidad_recibida // Actualizar a la cantidad real si cambió
        ]);

        return back()->with('success', "Pieza '{$solicitud->nombre_pieza}' añadida al inventario con stock de {$request->cantidad_recibida}.");
    }
}
