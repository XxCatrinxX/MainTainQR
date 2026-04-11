@extends('adminlte::page')

@section('title', 'Inventario')

@section('css')
    {{-- Estilos centralizados en admin-custom.css --}}
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Stock e Inventario</h1>
        <div class="d-flex gap-2">
            @if(auth()->user()->rol == 'admin' || auth()->user()->rol == 'almacenista')
            <a href="{{ route('solicitudes.index') }}" class="btn btn-modern btn-outline-secondary mr-2">
                <i class="fas fa-shopping-cart mr-1"></i> Ver Solicitudes
            </a>
            @endif
            <a href="{{ route('web.inventario.create') }}" class="btn btn-modern btn-dark-modern">
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
                            <a href="{{ route('web.inventario.edit', $pieza->id) }}" class="btn btn-action" title="Editar">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form action="{{ route('web.inventario.destroy', $pieza->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar pieza de forma permanente?');">
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
