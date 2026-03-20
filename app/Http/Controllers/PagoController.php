<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\OrdenServicio;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function index()
    {
        // En una app real, podrías filtrar por fecha o cliente.
        $pagos = Pago::with('orden_servicio')->latest()->get();
        return view('pagos.index', compact('pagos'));
    }

    public function create(Request $request)
    {
        $orden_lista = OrdenServicio::whereIn('estado', ['listo', 'entregado', 'reparacion'])
                                    ->get();

        $orden_preseleccionada = $request->has('orden_id') ? OrdenServicio::find($request->orden_id) : null;

        return view('pagos.create', compact('orden_lista', 'orden_preseleccionada'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'orden_servicio_id' => 'required|exists:orden_servicios,id',
            'monto' => 'required|numeric|min:1',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia',
            'tipo_pago' => 'required|in:anticipo,liquidacion',
        ]);

        Pago::create($data);

        return redirect()->route('pagos.index')->with('success', 'Pago registrado correctamente.');
    }

    /**
     * Registro rápido de pago desde la vista detalle de una orden.
     */
    public function storeDesdeOrden(Request $request, $id)
    {
        $data = $request->validate([
            'monto'      => 'required|numeric|min:1',
            'metodo_pago'=> 'required|in:efectivo,tarjeta,transferencia',
            'tipo_pago'  => 'required|in:anticipo,liquidacion',
        ]);
        $data['orden_servicio_id'] = $id;
        Pago::create($data);

        return redirect()->route('ordenes.show', $id)->with('success', 'Cobro registrado correctamente.');
    }
}
