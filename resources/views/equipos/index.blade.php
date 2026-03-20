@extends('adminlte::page')

@section('title', 'Equipos Registrados')

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
    
    .badge-modern { padding: 0.4em 0.8em; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; background-color: #e5e7eb; color: #374151; }
</style>
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
