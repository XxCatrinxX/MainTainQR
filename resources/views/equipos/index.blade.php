@extends('adminlte::page')

@section('title', 'Equipos Registrados')

@section('css')
    {{-- Estilos centralizados en admin-custom.css --}}
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Directorio de Equipos</h1>
    </div>
@stop

@section('content')
<div class="card overflow-hidden">
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Marca y Modelo</th>
                    <th>Tipo</th>
                    <th>Número de Serie</th>
                    <th>QR UUID</th>
                    <th>Propietario / Cliente</th>
                </tr>
            </thead>
            <tbody>
                @forelse($equipos as $equipo)
                    <tr>
                        <td style="font-weight: 600; color: #111827;">{{ $equipo->marca }} {{ $equipo->modelo }}</td>
                        <td class="text-capitalize">{{ $equipo->tipo }}</td>
                        <td><span class="badge-modern">{{ $equipo->numero_serie }}</span></td>
                        <td style="font-family: monospace; font-size: 0.85rem; color: #6b7280;">{{ $equipo->qr_token }}</td>
                        <td>
                            @if($equipo->cliente)
                                {{ $equipo->cliente->nombre }} {{ $equipo->cliente->apellido_paterno }}
                            @else
                                <span class="text-muted">Desconocido</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No se registran equipos en la plataforma aún.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
