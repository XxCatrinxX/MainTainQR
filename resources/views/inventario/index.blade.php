@extends('adminlte::page')

@section('title', 'Inventario')

@section('css')
<style>
    body { background-color: #fafafa !important; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important; }
    .content-wrapper { background-color: transparent !important; }
    .content-header h1 { font-weight: 700; font-size: 1.5rem; color: #111827; margin-bottom: 0.5rem; }
    
    .card { border: none !important; border-radius: 12px !important; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05) !important; margin-bottom: 1.5rem; }
    .table-modern { margin: 0; border-collapse: separate; border-spacing: 0; width: 100%; }
    .table-modern th { background-color: #f9fafb !important; color: #6b7280; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; border-top: none; border-bottom: 1px solid #f3f4f6; padding: 1rem 1.5rem; font-weight: 600; }
    .table-modern td { padding: 1rem 1.5rem; vertical-align: middle; border-bottom: 1px solid #f3f4f6; color: #374151; font-size: 0.95rem; }
    .table-modern tbody tr:last-child td { border-bottom: none; }
    .table-modern tbody tr:hover { background-color: #f8fafc; }
    
    .badge-modern { padding: 0.4em 0.8em; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; }
    .badge-stock-ok { background-color: #dcfce7; color: #166534; }
    .badge-stock-low { background-color: #fef08a; color: #854d0e; }
    .badge-stock-out { background-color: #fee2e2; color: #991b1b; }

    .btn-modern { border-radius: 8px !important; font-weight: 500 !important; padding: 0.5rem 1.25rem; transition: all 0.2s ease; border: none; }
    .btn-dark-modern { background-color: #000000 !important; color: #ffffff !important; }
    .btn-dark-modern:hover { background-color: #333333 !important; transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1) !important; color: white !important; }
    .btn-action { color: #6b7280; background: transparent; border: none; padding: 0.4rem; border-radius: 6px; transition: all 0.2s; }
    .btn-action:hover { background-color: #f3f4f6; color: #111827; }
</style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Stock e Inventario</h1>
        <div class="d-flex gap-2">
            @if(auth()->user()->role == 'admin' || auth()->user()->role == 'almacenista')
            <a href="{{ route('solicitudes.index') }}" class="btn btn-modern btn-outline-secondary mr-2">
                <i class="fas fa-shopping-cart mr-1"></i> Ver Solicitudes
            </a>
            @endif
            <a href="{{ route('inventario.create') }}" class="btn btn-modern btn-dark-modern">
                <i class="fas fa-plus mr-1"></i> Nueva Pieza
            </a>
        </div>
    </div>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success" style="border-radius: 8px; font-weight: 500;">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

<div class="card overflow-hidden">
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nombre de la Pieza</th>
                    <th>Calidad</th>
                    <th>Stock</th>
                    <th>Precio</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($piezas as $pieza)
                    <tr>
                        <td style="font-family: monospace; font-weight: 600; color: #4b5563;">{{ $pieza->sku }}</td>
                        <td style="font-weight: 500;">{{ $pieza->nombre_pieza }}</td>
                        <td>
                            <span class="text-capitalize text-muted">{{ $pieza->calidad }}</span>
                        </td>
                        <td>
                            @php
                                $stockClass = 'badge-stock-ok';
                                if($pieza->stock == 0) $stockClass = 'badge-stock-out';
                                elseif($pieza->stock < 5) $stockClass = 'badge-stock-low';
                            @endphp
                            <span class="badge-modern {{ $stockClass }}">
                                {{ $pieza->stock }} Unidades
                            </span>
                        </td>
                        <td style="font-weight: 600;">${{ number_format($pieza->precio_venta, 2) }}</td>
                        <td class="text-right">
                            <a href="{{ route('inventario.edit', $pieza->id) }}" class="btn btn-action" title="Editar">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form action="{{ route('inventario.destroy', $pieza->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar pieza de forma permanente?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-action text-danger" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-box-open" style="font-size: 2rem; opacity: 0.5; margin-bottom: 1rem; display: block;"></i>
                            Aún no hay piezas en el inventario.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
