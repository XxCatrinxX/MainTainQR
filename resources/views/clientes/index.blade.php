@extends('adminlte::page')

@section('title', 'Directorio de Clientes')

@section('css')
    {{-- Estilos centralizados en admin-custom.css --}}
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Directorio de Clientes</h1>
    </div>
@stop

@section('content')
<div class="card overflow-hidden">
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Nombre Completo</th>
                    <th>Teléfono</th>
                    <th>Correo Electrónico</th>
                    <th>Fecha de Registro</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                    <tr>
                        <td style="font-weight: 600; color: #111827;">{{ $cliente->nombre }} {{ $cliente->apellido_paterno }} {{ $cliente->apellido_materno }}</td>
                        <td>{{ $cliente->telefono ?? 'N/A' }}</td>
                        <td>{{ $cliente->correo ?? 'N/A' }}</td>
                        <td class="text-muted" style="font-size: 0.85rem;">
                            {{ \Carbon\Carbon::parse($cliente->created_at)->format('d M, Y') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">Aún no hay clientes registrados en la plataforma.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
