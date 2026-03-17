@extends('adminlte::page')

@section('title', 'Órdenes de Servicio')

@section('content_header')
    <h1>Órdenes de Servicio</h1>
@stop

@section('content')

<div class="card">
    <div class="card-header">
        <a href="{{ route('ordenes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Orden
        </a>
    </div>

    <div class="card-body">

        <table class="table table-bordered table-striped">
            <thead class="bg-dark text-white">
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Equipo</th>
                    <th>Problema</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @forelse($ordenes as $orden)
                    <tr>
                        <td>{{ $orden->id_orden }}</td>

                        {{-- Cliente --}}
                        <td>
                            {{ $orden->cliente->nombre ?? '' }}
                            {{ $orden->cliente->apellido_paterno ?? '' }}
                        </td>

                        {{-- Equipo --}}
                        <td>
                            {{ $orden->equipo->tipo_equipo ?? '' }} -
                            {{ $orden->equipo->marca ?? '' }}
                        </td>

                        {{-- Problema --}}
                        <td>{{ $orden->problema_reportado }}</td>

                        {{-- Estado --}}
                        <td>
                            
                            @if($orden->estado == 'recibido')
                                <span class="badge badge-secondary">Recibido</span>
                            @elseif($orden->estado == 'diagnostico')
                                <span class="badge badge-warning">Diagnóstico</span>
                            @elseif($orden->estado == 'reparacion')
                                <span class="badge badge-info">Reparación</span>
                            @elseif($orden->estado == 'terminado')
                                <span class="badge badge-success">Terminado</span>
                            @elseif($orden->estado == 'entregado')
                                <span class="badge badge-dark">Entregado</span>
                            @elseif($orden->estado == 'abierta')
                                <span class="badge badge-secondary">Abierta</span>

                            @endif
                        </td>

                        {{-- Fecha --}}
                        <td>{{ $orden->fecha_recepcion }}</td>

                        {{-- Acciones --}}
                        <td>
                            <a href="#" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>

                            <a href="#" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="7" class="text-center">No hay órdenes registradas</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>
</div>

@stop