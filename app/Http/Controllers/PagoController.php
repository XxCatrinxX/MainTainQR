<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\OrdenServicio;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function index()
    {
        $pagos = Pago::with('orden_servicio')->latest()->get();
        
        $total_ingresos = $pagos->where('tipo_pago', '!=', 'pago_cliente')->sum('monto');
        $total_egresos = $pagos->where('tipo_pago', 'pago_cliente')->sum('monto');

        $ordenes_pendientes = OrdenServicio::with(['cliente', 'equipo', 'pagos', 'repuestos'])
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

        return view('pagos.index', compact('pagos', 'total_ingresos', 'total_egresos', 'ordenes_pendientes'));
    }

    public function create(Request $request)
    {
        $orden_lista = OrdenServicio::with(['cliente', 'equipo', 'pagos', 'repuestos'])
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

        $orden = OrdenServicio::with('pagos')->find($id);

        // Si es compra de piezas y ya le pagaron todo el monto acordado, la finalizamos.
        if ($orden->estado === 'para_pzas') {
            $totalDebe = $orden->monto_compra_piezas ?? 0;
            $totalPagado = $orden->pagos->sum('monto');
            
            if ($totalPagado >= $totalDebe) {
                $orden->estado = 'entregado'; // Ya se le pagó, el equipo se queda con nosotros, cerramos orden.
                $orden->fecha_entrega_real = now();
                $orden->save();
                return redirect()->route('ordenes.show', $id)->with('success', 'Pago a cliente registrado. Al cubrirse el monto total, la orden ha sido Cerrada (Completada).');
            }
            return redirect()->route('ordenes.show', $id)->with('success', 'Abono a cliente registrado correctamente.');
        }

        return redirect()->route('ordenes.show', $id)->with('success', 'Cobro registrado correctamente.');
    }
}
