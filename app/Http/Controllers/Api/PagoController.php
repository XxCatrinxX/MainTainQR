<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\OrdenServicio;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function index()
    {
        return response()->json(Pago::with('ordenServicio')->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'orden_servicio_id' => 'required|exists:orden_servicios,id',
            'monto' => 'required|numeric|min:0',
            'metodo_pago' => 'required|string',
            'referencia' => 'nullable|string',
        ]);

        $pago = Pago::create($data);

        // Update order status if fully paid or similar logic
        return response()->json($pago, 201);
    }

    public function storeDesdeOrden(Request $request, $ordenId)
    {
        $data = $request->validate([
            'monto' => 'required|numeric|min:0',
            'metodo_pago' => 'required|string',
            'referencia' => 'nullable|string',
        ]);

        $data['orden_servicio_id'] = $ordenId;
        $pago = Pago::create($data);

        return response()->json($pago, 201);
    }
}
