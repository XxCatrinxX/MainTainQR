<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdenServicio;
use App\Models\Equipo;
use App\Models\Cliente;
use App\Models\User;

class OrdenServicioController extends Controller
{
    public function index()
{
    $ordenes = OrdenServicio::with(['cliente', 'equipo'])->get();

    return view('ordenes.index', compact('ordenes'));
}
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
        'cliente_nombre' => 'required',
        'cliente_apellido_paterno' => 'required',
        'cliente_telefono' => 'required',

        'equipo_tipo' => 'required',
        'equipo_marca' => 'required',
        'problema_reportado' => 'required',
    ]);

    // Crear cliente
    $cliente = Cliente::create([
        'nombre' => $request->cliente_nombre,
        'apellido_paterno' => $request->cliente_apellido_paterno,
        'apellido_materno' => $request->cliente_apellido_materno,
        'telefono' => $request->cliente_telefono,
        'correo' => $request->cliente_correo,
        'direccion' => $request->cliente_direccion,
        'fecha_registro' => now(),
    ]);

    // Crear equipo (YA CORREGIDO)
    $equipo = Equipo::create([
        'id_cliente'   => $cliente->id_cliente,
        'tipo_equipo'  => $request->equipo_tipo,
        'marca'        => $request->equipo_marca,
        'modelo'       => $request->equipo_modelo,
        'num_serie'    => $request->equipo_num_serie,
        'color'        => $request->equipo_color,
        'observaciones'=> $request->equipo_observaciones,
    ]);

    // Crear orden
    OrdenServicio::create([
        'id_cliente' => $cliente->id_cliente,
        'id_equipo' => $equipo->id_equipo,
        'id_usuario' => $request->id_usuario,
        'problema_reportado' => $request->problema_reportado,
        'estado' => 'abierta',
        'fecha_recepcion' => now(),
    ]);



    return redirect()->route('home')
        ->with('success', 'Orden creada correctamente');
}}

