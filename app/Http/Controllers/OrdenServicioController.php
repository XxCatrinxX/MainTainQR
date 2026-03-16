<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdenServicio;
use App\Models\Equipo;
use App\Models\Cliente;
use App\Models\User;

class OrdenServicioController extends Controller
{
    public function create()
    {
        $equipos = Equipo::all();
        $usuarios = User::all();
        $clientes = Cliente::all();

        return view('ordenes.create', compact('equipos', 'usuarios', 'clientes'));
    }

    public function store(Request $request)
    {
    $request->validate([
        'id_equipo' => 'required',
        'id_usuario' => 'required',
        'problema_reportado' => 'required',
    ]);

    // Calcular el total automáticamente
    $total = $request->costo_materiales + $request->costo_servicio;

    OrdenServicio::create([
        'id_equipo'          => $request->id_equipo,
        'id_usuario'         => $request->id_usuario,
        'problema_reportado' => $request->problema_reportado,
        'diagnostico'        => $request->diagnostico,
        'actividad_a_realizar' => $request->actividad_a_realizar,
        'estado'             => $request->estado ?? 'abierta',
        'costo_materiales'   => $request->costo_materiales ?? 0,
        'costo_servicio'     => $request->costo_servicio ?? 0,
        'costo_total'        => $total,
        'fecha_recepcion'    => $request->fecha_recepcion ?? now(),
    ]);

    return redirect()->route('home')->with('info', 'Orden de servicio creada exitosamente.');
    }
}
