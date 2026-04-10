<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrdenServicio;
use App\Models\Cliente;
use App\Models\Inventario;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        // 1. KPIs - Datos para las tarjetas superiores
        $totalOrdenesHoy = OrdenServicio::whereDate('created_at', Carbon::today())->count();
        
        // Ajusta 'listo' por el nombre exacto de tu estado en la BD
        $pendientesEntrega = OrdenServicio::where('estado', 'recibido')->count();
        
        // Suma de la columna de cobro (ajusta 'costo_total' al nombre de tu columna)
        $ingresosMes = OrdenServicio::whereMonth('created_at', Carbon::now()->month)
                                    ->whereYear('created_at', Carbon::now()->year)
                                    ->sum('mano_obra'); // O el campo que corresponda a ingresos

        $stockBajo = Inventario::where('stock', '<', 5)->count();

        // 2. Últimas 5 órdenes con su relación cliente cargada (Eager Loading)
        $ordenesRecientes = OrdenServicio::with('cliente')
                                        ->latest()
                                        ->take(5)
                                        ->get();

        // 3. Lógica para la Gráfica de los últimos 7 días
        // Cambiamos el orderBy para que use la misma lógica de agrupación
$ventasSemanales = OrdenServicio::select(
        DB::raw('DATE_FORMAT(created_at, "%d/%m") as fecha'),
        DB::raw('count(*) as total')
    )
    ->where('created_at', '>=', Carbon::now()->subDays(6))
    ->groupBy('fecha')
    // OPCIÓN A: Ordenar por la fecha formateada (si el formato permite orden cronológico)
    // OPCIÓN B: Ordenar por el valor mínimo de created_at en ese grupo (Más seguro)
    ->orderBy(DB::raw('MIN(created_at)'), 'asc') 
    ->get();
        $labelsGrafica = $ventasSemanales->pluck('fecha'); 
        $datosGrafica = $ventasSemanales->pluck('total');

        return view('home', compact(
            'totalOrdenesHoy', 
            'pendientesEntrega', 
            'ingresosMes', 
            'stockBajo', 
            'ordenesRecientes',
            'labelsGrafica',
            'datosGrafica'
        ));
    }
}