@extends('adminlte::page')

@section('title', 'Papelera de Órdenes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-trash-alt mr-2 text-muted"></i>Papelera de Órdenes</h1>
        <a href="{{ route('ordenes.index') }}" class="btn btn-secondary shadow-sm" style="border-radius: 8px;">
            <i class="fas fa-arrow-left mr-1"></i> Volver a Órdenes
        </a>
    </div>
@stop

@section('content')
<div class="card shadow-sm border-0" style="border-radius: 12px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-top-0">Folio</th>
                        <th class="border-top-0">Cliente</th>
                        <th class="border-top-0">Estado al Borrar</th>
                        <th class="border-top-0">Fecha Entrega</th>
                        <th class="border-top-0 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ordenes as $orden)
                        <tr>
                            <td class="align-middle font-weight-bold text-primary">{{ $orden->folio }}</td>
                            <td class="align-middle">
                                <div style="font-weight: 600;">{{ $orden->equipo->cliente->nombre ?? 'N/A' }}</div>
                                <div class="small text-muted">{{ $orden->equipo->marca }} {{ $orden->equipo->modelo }}</div>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-secondary text-uppercase" style="font-size: 0.75rem;">{{ $orden->estado }}</span>
                            </td>
                            <td class="align-middle text-muted" style="font-size: 0.9rem;">
                                {{ $orden->fecha_entrega_real ? \Carbon\Carbon::parse($orden->fecha_entrega_real)->format('d M, Y') : 'N/A' }}
                            </td>
                            <td class="align-middle text-right">
                                <div class="d-flex justify-content-end">
                                    <form action="{{ route('ordenes.restore', $orden->id) }}" method="POST" class="mr-2">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success shadow-sm" title="Restaurar Orden">
                                            <i class="fas fa-trash-restore"></i> Restaurar
                                        </button>
                                    </form>

                                    <form action="{{ route('ordenes.forceDelete', $orden->id) }}" method="POST" 
                                          onsubmit="return confirm('¿Estás seguro? Esta acción no se puede deshacer.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger shadow-sm" title="Eliminar Permanentemente">
                                            <i class="fas fa-fire"></i> Borrar Permanente
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-info-circle fa-2x mb-3 d-block"></i>
                                No hay órdenes en la papelera.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop