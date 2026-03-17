<?php
namespace App\Http\Controllers;

use App\Models\OrdenServicio;

class HomeController extends Controller
{
    public function index()
    {
        $ordenes = OrdenServicio::with(['equipo.cliente'])
            ->latest()
            ->take(5)
            ->get();

        return view('home', [
            'totalAbiertas' => OrdenServicio::where('estado', 'abierta')->count(),
            'totalPendientes' => OrdenServicio::where('estado', 'esperando_repuesta')->count(),
            'totalProceso' => OrdenServicio::where('estado', 'en_proceso')->count(),
            'totalCerradas' => OrdenServicio::where('estado', 'cerrada')->count(),
            'ordenesRecientes' => $ordenes,
            'chartData' => [
                'abiertas' => OrdenServicio::where('estado', 'abierta')->count(),
                'cerradas' => OrdenServicio::where('estado', 'cerrada')->count(),
            ]
        ]);
    }
}