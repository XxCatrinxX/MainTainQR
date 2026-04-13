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
        $orden_lista = OrdenServicio::with(['equipo.cliente', 'pagos', 'repuestos'])
            ->whereIn('estado', ['listo', 'entregado', 'reparacion', 'para_pzas'])
            ->get()
            ->map(function ($orden) {
                if ($orden->estado === 'para_pzas') {
                    $total = $orden->monto_compra_piezas ?? 0;
                } else {
                    $total = ($orden->mano_obra ?? 0) + $orden->repuestos->sum(function($r) {
                        return $r->pivot->cantidad * $r->pivot->precio_fijado;
                    });
                }
                
                $pagado = $orden->pagos->sum('monto');
                $orden->restante = $total - $pagado;
                $orden->total_calculado = $total;
                return $orden;
            })->filter(function ($orden) {
                return $orden->restante > 0;
            });

        $orden_preseleccionada = $request->has('orden_id') ? OrdenServicio::find($request->orden_id) : null;

        return view('pagos.create', compact('orden_lista', 'orden_preseleccionada'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'orden_servicio_id' => 'required|exists:orden_servicios,id',
            'monto' => 'required|numeric|min:1',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia',
            'tipo_pago' => 'required|in:anticipo,liquidacion,pago_cliente',
        ]);

        Pago::create($data);
        
        $orden = OrdenServicio::find($data['orden_servicio_id']);
        if ($orden->estado === 'para_pzas') {
            $orden->estado = 'listo';
            $orden->fecha_listo = now();
            $orden->save();
        }

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
            'tipo_pago'  => 'required|in:anticipo,liquidacion,pago_cliente',
        ]);
        $data['orden_servicio_id'] = $id;
        Pago::create($data);

        return redirect()->route('ordenes.show', $id)->with('success', 'Cobro registrado correctamente.');
    }
}
